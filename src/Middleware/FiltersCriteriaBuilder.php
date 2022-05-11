<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\ExpressionBuilder;
use Tpg\HeadlessBundle\Filter\Condition;
use Tpg\HeadlessBundle\Filter\Filters;

final class FiltersCriteriaBuilder
{
    public function __invoke(Filters $filters):Criteria
    {
        return Criteria::create()->where($this->resolveFilters($filters));
    }
    private function resolveFilters(Filters $filters): Expression
    {
        $expressions = [];
        foreach ($filters->getConditions() as $condition) {
            $expressions[] = $this->resolveCondition($condition);
        }

        foreach ($filters->getGroups() as $subgroup) {
            $expressions[] = $this->resolveFilters($subgroup);
        }

        return $filters->isAnd() ? Criteria::expr()->andX(...$expressions) : Criteria::expr()->orX(...$expressions);
    }

    private function resolveCondition(Condition $condition): Comparison
    {
        $property = $condition->property();
        $value = $condition->value();

        /**
         * @var $expr ExpressionBuilder
         */
        $expr = Criteria::expr();
        switch ($condition->operator()) {
            case '=':
                return $expr->eq($property, $value);
            case '>':
                return $expr->gt($property, $value);
            case '>=':
                return $expr->gte($property, $value);
            case '<':
                return $expr->lt($property, $value);
            case '<=':
                return $expr->lte($property, $value);
            case '<>':
                return $expr->neq($property, $value);
            case 'startsWith':
                return $expr->startsWith($property, $value);
            case 'endsWith':
                return $expr->endsWith($property, $value);
            case 'contains':
                return $expr->contains($property, $value);
            case 'in':
                return $expr->in($property, (array)$value);
            case 'nin':
                return $expr->notIn($property, (array)$value);
            case 'nnull':
                return $expr->neq($property, null);
            case 'null':
                return $expr->isNull($property);
            default:
                throw new \Exception(sprintf('Unimplemented %s operator', $condition->operator()));
        }
    }
}