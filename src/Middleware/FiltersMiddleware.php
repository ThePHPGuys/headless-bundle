<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;

use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Filter\Filters;

final class FiltersMiddleware implements Middleware, RestrictQueryTypeMiddleware
{
    public function restrictedToQueryTypes(): array
    {
        return [MiddlewareContextBuilder::MANY, MiddlewareContextBuilder::COUNT];
    }

    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
    {
        if($this->isValid($context)){
            $filters = $context[FiltersContextBuilder::FILTERS];
            $queryBuilder->addCriteria((new FiltersCriteriaBuilder())($filters));
        }
        return $stack->handle($queryBuilder,$context);
    }

    private function isValid(array $context):bool
    {
        if(!isset($context[FiltersContextBuilder::FILTERS])){
            return false;
        }

        /** @var Filters $filters */
        $filters = $context[FiltersContextBuilder::FILTERS];

        return $filters->getConditions() || !$filters->getGroups();
    }

}