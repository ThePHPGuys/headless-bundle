<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Service\HydratorORM;

final class HydratorMiddleware implements Middleware
{
    private HydratorORM $hydratorORM;

    public function __construct(HydratorORM $hydratorORM)
    {
        $this->hydratorORM = $hydratorORM;
    }

    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
    {
        $result = $stack->handle($queryBuilder,$context);
        if(!$this->isValid($context)){
            return $result;
        }
        return $this->hydratorORM->hydrate($result);
    }

    private function isValid(array $context):bool
    {
        return isset($context[MiddlewareContextBuilder::QUERY_TYPE],$context[MiddlewareContextBuilder::COLLECTION])
            && in_array($context[MiddlewareContextBuilder::QUERY_TYPE],[
                MiddlewareContextBuilder::ONE,
                MiddlewareContextBuilder::MANY,
                ],true
            );
    }
}