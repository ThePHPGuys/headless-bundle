<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;

use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Filter\Filters;

final class FiltersMiddleware implements Middleware
{
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
        if(!isset($context[MiddlewareContextBuilder::QUERY_TYPE],$context[FiltersContextBuilder::FILTERS])){
            return false;
        }
        if(!in_array(
            $context[MiddlewareContextBuilder::QUERY_TYPE],
            [
                MiddlewareContextBuilder::MANY,
                MiddlewareContextBuilder::COUNT
            ],true)
        ){
            return false;
        }
        /** @var Filters $filters */
        $filters = $context[FiltersContextBuilder::FILTERS];

        if(!$filters->getConditions() && !$filters->getGroups()){
            return false;
        }

        return true;
    }

}