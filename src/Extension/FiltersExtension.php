<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Tpg\HeadlessBundle\Extension\Filter\Condition;
use Tpg\HeadlessBundle\Extension\Filter\Filters;
use Tpg\HeadlessBundle\Service\ExecutorORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\Common\Collections\Expr\Comparison;
use Doctrine\Common\Collections\Expr\Expression;
use Doctrine\Common\Collections\ExpressionBuilder;
use Doctrine\ORM\QueryBuilder;
use Exception;

final class FiltersExtension implements ExecutorOrmExtension
{
    public const CONTEXT_KEY = self::class;

    public function supports(string $collection, QueryBuilder $queryBuilder, array $context = []): bool
    {
        if(!array_key_exists(self::CONTEXT_KEY,$context)){
            return false;
        }
        if(!in_array($context[ExecutorOrmExtension::OPERATION_CONTEXT_KEY],[ExecutorOrmExtension::OPERATION_GET_MANY,ExecutorOrmExtension::OPERATION_COUNT],true)){
            return false;
        }
        /** @var Filters $filters */
        $filters = $context[self::CONTEXT_KEY];

        if(!$filters->getConditions() && !$filters->getGroups()){
            return false;
        }

        return true;
    }

    public function apply(string $collection, QueryBuilder $queryBuilder, array &$context = [])
    {
        /** @var Filters $filters */
        $filters = $context[self::CONTEXT_KEY];

        $criteria = $this->resolveFilters($filters);

        $queryBuilder->addCriteria(Criteria::create()->where($criteria));
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
                throw new Exception(sprintf('Unimplemented %s operator', $condition->operator()));
        }
    }
}