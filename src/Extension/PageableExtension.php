<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Query\Sort;
use Tpg\HeadlessBundle\Service\ExecutorORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;

final class PageableExtension implements ExecutorOrmExtension
{
    public const CONTEXT_KEY = self::class;
    private const MAX_PAGE_SIZE = 50;

    public function supports(string $collection, QueryBuilder $queryBuilder, array $context = []): bool
    {
        return array_key_exists(self::CONTEXT_KEY, $context) && $context[ExecutorORM::OPERATION_CONTEXT_KEY]===ExecutorORM::OPERATION_MANY;
    }

    public function apply(string $collection, QueryBuilder $queryBuilder, array $context = [])
    {
        /** @var Pageable $pageable */
        $pageable = $context[self::CONTEXT_KEY];
        $criteria = Criteria::create();
        $this->addPaginationToCriteria($criteria,$pageable);
        $this->addSortingToCriteria($criteria,$pageable);
        $queryBuilder->addCriteria($criteria);
    }

    private function addPaginationToCriteria(Criteria $criteria, Pageable $pageable):void
    {
        $maxPageSize = min($pageable->getPageSize(),self::MAX_PAGE_SIZE);
        $criteria->setFirstResult($maxPageSize*$pageable->getPageNumber());
        $criteria->setMaxResults($maxPageSize);
    }

    private function addSortingToCriteria(Criteria $criteria,Pageable $pageable):void
    {
        $sort = $pageable->getSortOr(Sort::unsorted());

        if(!$sort->isSorted()){
            return;
        }
        $orders = $sort->getIterator();
        //Todo: Check allowed fields
        $orderings = [];
        foreach ($orders as $order){
            $orderings[$order->property()]=(string)$order->direction();
        }
        $criteria->orderBy($orderings);
    }
}