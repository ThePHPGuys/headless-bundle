<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Ast\Walker\CollectionToORMQueryBuilder;
use Tpg\HeadlessBundle\Exception\NotFoundException;
use Tpg\HeadlessBundle\Extension\ExecutorOrmExtensionContextBuilder;
use Tpg\HeadlessBundle\Extension\ExecutorOrmExtension;
use Tpg\HeadlessBundle\Extension\ExecutorOrmHydrator;
use Tpg\HeadlessBundle\Extension\ExecutorOrmHydratorContextBuilder;
use Tpg\HeadlessBundle\Schema\Relation;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;

final class ExecutorORM
{
    private const HIDDEN_RELATED_FIELD = '__relId';
    private SchemaService $schemaService;
    private EntityManagerInterface $entityManager;
    /** @var ExecutorOrmExtension[] */
    private iterable $extensions;
    /** @var ExecutorOrmHydrator[] */
    private iterable $hydrators;


    public function __construct(
        SchemaService $schemaService,
        EntityManagerInterface $entityManager,
        iterable $extensions = [],
        iterable $hydrators = []
    ) {
        $this->schemaService = $schemaService;
        $this->entityManager = $entityManager;
        $this->extensions = $extensions;
        $this->hydrators = $hydrators;
    }

    public function getMany(Collection $collectionAst, array $context = []): array
    {
        $walker = $this->getCollectionWalker($collectionAst);
        $builder = $walker->getBuilder();

        //Attach filters
        return $this->executeAndJoinRelations(
            $builder,
            $collectionAst->collectionName,
            $walker->getDeferredRelations(),
            (new ExecutorOrmExtensionContextBuilder())->withContext($context)->withGetMany()->toArray()
        );
    }

    //TODO: Move to QueryBuilder executor, also move extensions
    public function getCount(string $collection, array $context = []): int
    {
        $queryBuilder = $this->createQueryBuilder($collection);
        $collectionMeta = $this->schemaService->getCollection($collection);
        $queryBuilder
            ->select(
                sprintf(
                    'COUNT(%s.%s)',
                    $collection,
                    $this->schemaService->getIdentifier($collection)->fieldName
                )
            )
            ->from($collectionMeta->class, $collection);

        $this->applyExtensions(
            $collection,
            $queryBuilder,
            (new ExecutorOrmExtensionContextBuilder())->withContext($context)->withCount()->toArray()
        );
        return (int)$queryBuilder->getQuery()->getSingleScalarResult();
    }

    public function getOne(Collection $collectionAst, $id, $context = []): array
    {
        $walker = $this->getCollectionWalker($collectionAst);
        $builder = $walker->getBuilder();
        $this->addIdentifierCondition($collectionAst->collectionName, $id, $builder);
        $result = $this->executeAndJoinRelations(
            $builder,
            $collectionAst->collectionName,
            $walker->getDeferredRelations(),
            (new ExecutorOrmExtensionContextBuilder())->withContext($context)->withGetOne()->toArray()
        );

        if (count($result) === 0) {
            throw new NotFoundException();
        }

        if (count($result) > 1) {
            throw new \LogicException('Result is ambiguous, expects one');
        }

        return $result[0];

    }

    private function addIdentifierCondition(string $collection, $id, QueryBuilder $queryBuilder)
    {
        $idField = $this->schemaService->getIdentifier($collection);
        $expr = Criteria::expr();
        $criteria = Criteria::create()->where($expr->eq(
            sprintf('%s.%s', $collection, $idField->fieldName),
            $id
        ));
        $queryBuilder->addCriteria($criteria);
    }

    private function getCollectionWalker(Collection $collection): CollectionToORMQueryBuilder
    {
        return new CollectionToORMQueryBuilder(
            $this->schemaService,
            $this->createQueryBuilder($collection->collectionName),
            $collection
        );
    }

    private function applyExtensions(string $collection, QueryBuilder $queryBuilder, array $context = [])
    {
        foreach ($this->extensions as $extension) {
            if (!$extension->supports($collection, $queryBuilder, $context)) {
                continue;
            }
            $extension->apply($collection, $queryBuilder, $context);
        }
    }

    private function applyHydrators(array $data, array $context):array
    {
        foreach ($this->hydrators as $hydrator) {
            if (!$hydrator->supportsHydration($data, $context)) {
                continue;
            }
            $data = $hydrator->hydrate($data,$context);
        }
        return $data;
    }

    private function applyHydratorsToData(array $data, array $context):array
    {
        return array_map(fn(array $row)=>$this->applyHydrators($row, $context),$data);
    }

    /**
     * @param  QueryBuilder  $queryBuilder
     * @param  string  $parentCollection
     * @param  array<RelationToOne>  $deferredRelations
     * @return array
     */
    private function executeAndJoinRelations(
        QueryBuilder $queryBuilder,
        string $parentCollection,
        array $deferredRelations,
        array $context
    ): array {
        $this->applyExtensions($parentCollection, $queryBuilder, $context);
        $rawData = $queryBuilder->getQuery()->getArrayResult();

        $data = $this->applyHydratorsToData(
            $rawData,
            (new ExecutorOrmHydratorContextBuilder())->withCollection($parentCollection)->toArray()
        );


        $relationsKeys = array_map(fn(RelationToOne $relationToOne) => $relationToOne->fieldName, $deferredRelations);


        //If no relation keys, just return data
        if (!$relationsKeys) {
            return $data;
        }

        //Gather relation keys
        $relationIds = [];
        foreach ($relationsKeys as $relationsKey) {
            $relationIds[$relationsKey] = array_column($data, $relationsKey);
        }
        $relationResolvers = $this->getRelationResolvers(
            $parentCollection,
            $deferredRelations,
            (new ExecutorOrmExtensionContextBuilder())->withGetJoined()->toArray()
        );

        //Resolve relations
        foreach ($relationIds as $relation => $relationId) {
            //For to one, relation and related field is same
            //For to many can be primary key i.e. Author has many Posts
            $data = $this->hydrateRelation($data, $relation, $relation, $relationResolvers[$relation]($relationId));
        }

        return $data;
    }

    private function createQueryBuilder(string $collection): QueryBuilder
    {
        return $this->entityManager->createQueryBuilder();
    }

    private function hydrateRelation(
        array $data,
        string $relation,
        string $relatedField,
        array $relationValues,
        $defaultValue = null
    ): array {
        foreach ($data as &$row) {
            $row[$relation] = $relationValues[(string) $row[$relatedField]] ?? $defaultValue;
        }
        return $data;
    }

    /**
     * Returns callbacks for each relation with IN query, where result is indexed by relation key
     * @param  array<RelationToOne>  $deferredRelations
     * @return array<string,callable(array)>
     */
    private function getRelationResolvers(string $parentCollection, array $deferredRelations, array $context): array
    {
        $resolvers = [];
        foreach ($deferredRelations as $relation) {
            $relationMeta = $this->schemaService->getRelation($parentCollection, $relation->fieldName);

            $collection = $this->getCollectionAstFromRelation($relation);
            $walker = $this->getCollectionWalker($collection);

            //TODO: Attach walker condition
            $builder = $walker->getBuilder();
            $this->addSelectRelatedFields($builder, $relationMeta);

            $resolvers[$relation->fieldName] = fn(array $ids) => $this->aggregateToOneField(
                $this->executeAndJoinRelations(
                    $builder->setParameter('relId', $ids),
                    $relation->collectionName,
                    $walker->getDeferredRelations(),
                    $context
                )
            );
        }
        return $resolvers;
    }

    private function addSelectRelatedFields(QueryBuilder $queryBuilder, Relation $relation): void
    {
        $referencedField = sprintf('%s.%s', $relation->collection, $relation->referencedColumn);
        $queryBuilder
            ->addSelect(sprintf("%s as %s", $referencedField, self::HIDDEN_RELATED_FIELD))
            ->where(sprintf('%s IN (:relId)', self::HIDDEN_RELATED_FIELD));
    }

    /**
     * @psalm-pure
     * @param  array  $rows
     * @return array
     */
    private function aggregateToOneField(array $rows): array
    {
        $aggregatedRows = [];
        foreach ($rows as $row) {
            $aggregateId = (string) $row[self::HIDDEN_RELATED_FIELD];
            unset($row[self::HIDDEN_RELATED_FIELD]);
            $aggregatedRows[$aggregateId] = $row;
        }
        return $aggregatedRows;
    }

    private function getCollectionAstFromRelation(RelationToOne $relationToOne): Collection
    {
        $collection = Collection::create($relationToOne->collectionName);
        $collection->children = $relationToOne->children;
        return $collection;
    }

}