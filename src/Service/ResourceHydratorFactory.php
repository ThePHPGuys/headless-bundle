<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Walker\SelectFieldsBuilder;
use Tpg\HeadlessBundle\Hydrator\ConditionBuilder;
use Tpg\HeadlessBundle\Hydrator\ToManyConditionBuilder;
use Tpg\HeadlessBundle\Hydrator\ToOneConditionBuilder;
use Tpg\HeadlessBundle\Reference\CollectionResourceReference;
use Tpg\HeadlessBundle\Reference\Link;
use Tpg\HeadlessBundle\Reference\RelationToManyReference;
use Tpg\HeadlessBundle\Reference\RelationToOneReference;

final class ResourceHydratorFactory
{
    public const HIDDEN_RELATED_FIELD = '___relId';
    public const HIDDEN_REFERENCED_FIELD = '___refId';
    private QueryService $queryService;
    private SchemaService $schemaService;
    private ToOneConditionBuilder $toOneCondition;
    private ToManyConditionBuilder $toManyConditionBuilder;

    public function __construct(QueryService $queryService, SchemaService $schemaService)
    {
        $this->queryService = $queryService;
        $this->schemaService = $schemaService;
        $this->toOneCondition = new ToOneConditionBuilder($schemaService);
        $this->toManyConditionBuilder = new ToManyConditionBuilder($schemaService);
    }

    public function generateProvider(CollectionResourceReference $reference):callable
    {
        if ($reference instanceof RelationToOneReference) {
            return fn(RelationToOneReference ...$references)=>$this->getToOne($reference,...$references);
        }

        if($reference instanceof RelationToManyReference) {
            return fn(RelationToManyReference ...$references)=>$this->getToMany($reference,...$references);
        }

        throw new \LogicException('Unknown reference');
    }

    private function getToOne(RelationToOneReference $relationReference, RelationToOneReference ...$references):array
    {
        $relatedIds = array_column($references, 'value');

        if(!$relationReference->relation->hasChildren()){
            return (new Link($relatedIds,$relatedIds))->oneToOne(fn($v)=>$v,fn($v)=>$v);
        }
        $relation = $relationReference->relation;
        $collection = $relationReference->relation->toCollection();

        $builder = $this->toOneCondition->build(
            $this->schemaService->getRelation($relation->collection,$relation->fieldName),
            $this->queryService->createCollectionQueryBuilder(
                $collection
            ),
            $relatedIds
        );

        $relatedData = $this->queryService->executeForCollection($builder,$collection);

        return (new Link($relatedIds,$relatedData))
            ->withTransformation(fn($value)=>$this->removeFields($value,ConditionBuilder::HIDDEN_OWN_ID,ConditionBuilder::HIDDEN_RELATED_ID))
            ->oneToOne(fn($v)=>$v,fn($v)=>(string)$v[self::HIDDEN_RELATED_FIELD]);

    }

    private function getToMany(RelationToManyReference $relationReference, RelationToManyReference ...$references):array
    {
        $relatedIds = array_column($references, 'value');

        $relation = $relationReference->relation;
        $relatedCollection = $relation->toCollection();

        $builder = $this->toManyConditionBuilder->build(
            $this->schemaService->getRelation($relation->collection,$relation->fieldName),
            $this->queryService->createEmptyQueryBuilder($relationReference->relation->collection),
            $relatedIds
        );

        $resultTransformer = static fn($value)=>array_column($value,ConditionBuilder::HIDDEN_RELATED_ID);

        if($relatedCollection->hasChildren()){
            $builder = (new SelectFieldsBuilder($this->schemaService))->build($builder,$relatedCollection);
            $resultTransformer = fn($value)=>array_map(
                fn($v) => $this->removeFields($v,ConditionBuilder::HIDDEN_RELATED_ID, ConditionBuilder::HIDDEN_OWN_ID),
                $value
            );
        }

        $relatedData = $this->queryService->executeForCollection($builder,$relatedCollection);

        return (new Link($relatedIds,$relatedData))
            ->withTransformation($resultTransformer)
            ->oneToMany(fn($v)=>$v,fn($v)=>(string)$v[ToManyConditionBuilder::HIDDEN_OWN_ID]);
    }

    private function removeFields(array $data, string ...$keys):array
    {
        return array_filter(
            $data,
            static fn(string $key) => !in_array($key, $keys, true),
            ARRAY_FILTER_USE_KEY
        );
    }
}