<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;

use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Walker\SelectFieldsBuilder;
use Tpg\HeadlessBundle\Middleware\AttachReferencesToResultMiddleware;
use Tpg\HeadlessBundle\Middleware\HydratorMiddleware;
use Tpg\HeadlessBundle\Middleware\MiddlewareContextBuilder;
use Tpg\HeadlessBundle\Middleware\PageableMiddleware;
use Tpg\HeadlessBundle\Middleware\QueryMiddlewareStack;

final class QueryService
{
    private SchemaService $schemaService;
    private EntityManagerInterface $entityManager;
    private QueryMiddlewareStack $middlewareStack;

    public function __construct(
        SchemaService $schemaService,
        EntityManagerInterface $entityManager,
        QueryMiddlewareStack $middlewareStack
    ){
        $this->schemaService = $schemaService;
        $this->entityManager = $entityManager;
        $this->middlewareStack = $middlewareStack;
    }

    public function createCollectionQueryBuilder(Collection $collection):QueryBuilder
    {
        return $this->addFieldsToQueryBuilder(
            $collection,
            $this->createEmptyQueryBuilder($collection->name)
        );
    }

    public function addFieldsToQueryBuilder(Collection $collection, QueryBuilder $queryBuilder):QueryBuilder
    {
        return (new SelectFieldsBuilder($this->schemaService))->build(
            $queryBuilder,
            $collection
        );

    }

    public function executeForCollection(QueryBuilder $builder, Collection $collection, array $context=[]):array
    {
        $context = MiddlewareContextBuilder::create($context)
            ->withCollection($collection)
            ->toArray();
        return $this->execute($builder,$context);
    }

    public function execute(QueryBuilder $queryBuilder, array $context=[]):array
    {
        return $this->middlewareStack
            ->withDefault(fn(QueryBuilder $queryBuilder)=>$queryBuilder->getQuery()->getArrayResult())
            ->handle($queryBuilder,$context);
    }

    public function createEmptyQueryBuilder(string $collectionName): QueryBuilder
    {
        $collectionMeta = $this->schemaService->getCollection($collectionName);
        return $this->entityManager->createQueryBuilder()->from(
            $collectionMeta->class,
            $collectionName);
    }
}