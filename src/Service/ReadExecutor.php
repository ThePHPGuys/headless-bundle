<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Exception\NonUniqueResultException;
use Tpg\HeadlessBundle\Exception\NotFoundException;
use Tpg\HeadlessBundle\Middleware\MiddlewareContextBuilder;
use Doctrine\Common\Collections\Criteria;

final class ReadExecutor
{
    private SchemaService $schemaService;
    private QueryService $queryService;

    public function __construct(
        SchemaService $schemaService,
        QueryService $queryService
    ) {
        $this->schemaService = $schemaService;
        $this->queryService = $queryService;
    }

    public function many(Collection $collection, array $context = []): array
    {
        $queryBuilder = $this->queryService->createCollectionQueryBuilder($collection);

        $context = MiddlewareContextBuilder::create($context)
            ->withQueryType(MiddlewareContextBuilder::MANY)
            ->toArray();

        return $this->queryService->executeForCollection($queryBuilder,$collection,$context);
    }

    public function count(Collection $collection, array $context = []): int
    {
        $queryBuilder = $this->queryService->createEmptyQueryBuilder($collection->name);
        $queryBuilder
            ->select(
                sprintf(
                    'COUNT(%s)',
                    $this->getCollectionIdentifierName($collection->name)
                )
            );

        $context = MiddlewareContextBuilder::create($context)
            ->withQueryType(MiddlewareContextBuilder::COUNT)
            ->toArray();

        $result = $this->queryService->executeForCollection($queryBuilder,$collection,$context);

        if (count($result) > 1) {
            throw new NonUniqueResultException();
        }

        return (int)array_shift($result[0]);
    }

    public function one(Collection $collection, $id, $context = []): array
    {
        $queryBuilder = $this->queryService->createCollectionQueryBuilder($collection);

        $queryBuilder->addCriteria(
            Criteria::create()
                ->where(
                    Criteria::expr()->eq($this->getCollectionIdentifierName($collection->name),$id)
                )
        );

        $context = MiddlewareContextBuilder::create($context)
            ->withQueryType(MiddlewareContextBuilder::ONE)
            ->toArray();

        $result = $this->queryService->executeForCollection($queryBuilder,$collection, $context);

        if (count($result) === 0) {
            throw new NotFoundException();
        }

        if (count($result) > 1) {
            throw new NonUniqueResultException();
        }

        return $result[0];
    }

    private function getCollectionIdentifierName(string $collectionName):string
    {
        return sprintf('%s.%s',
            $collectionName,
            $this->schemaService->getIdentifier($collectionName)->fieldName
        );

    }

}
