<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Query\Sort;
use Tpg\HeadlessBundle\Query\Sort\Order;
use Tpg\HeadlessBundle\Security\Subject\AccessOperation;
use Tpg\HeadlessBundle\Service\AstFactory;
use Tpg\HeadlessBundle\Service\SecuredAstFactory;

final class PageableMiddleware implements Middleware
{
    private int $maxPageSize;
    private SecuredAstFactory $securedAstFactory;

    public function __construct(SecuredAstFactory $securedAstFactory, int $maxPageSize=50)
    {
        $this->maxPageSize = $maxPageSize;
        $this->securedAstFactory = $securedAstFactory;
    }

    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
    {
        if(!$this->isPageable($context)){
            return $stack->handle($queryBuilder,$context);
        }

        /** @var Pageable $pageable */
        $pageable = $context[PageableContextBuilder::PAGEABLE];
        /** @var Collection $collection */
        $collection = $context[MiddlewareContextBuilder::COLLECTION];

        $this->addPagination($queryBuilder,$pageable->getPageSize(),$pageable->getPageNumber());
        $this->addSorting($collection->name, $queryBuilder, $pageable->getSortOr(Sort::unsorted()));

        return $stack->handle($queryBuilder,$context);
    }

    private function addPagination(QueryBuilder $queryBuilder, int $pageSize, int $pageNumber):void
    {
        $maxPageSize = min($pageSize,$this->maxPageSize);
        $queryBuilder
            ->setFirstResult($maxPageSize*$pageNumber)
            ->setMaxResults($maxPageSize);
    }

    private function addSorting(string $collection, QueryBuilder $queryBuilder,Sort $sort):void
    {
        if(!$sort->isSorted()){
            return;
        }
        foreach ($sort as $order){
            $this->addOrder($collection, $queryBuilder, $order);
        }
    }

    private function addOrder(string $collection, QueryBuilder $queryBuilder, Order $order):void
    {
        $orderAst = $this->securedAstFactory->createCollectionAstFromFields(
            $collection,
            new Fields([$order->property()]),
            AccessOperation::SORT);
        (new PageableJoinBuilder())->addJoinsToBuilder($orderAst,$queryBuilder,$order->direction());
    }

    private function isPageable(array $context):bool
    {
        return isset(
                $context[PageableContextBuilder::PAGEABLE],
                $context[MiddlewareContextBuilder::COLLECTION],
                $context[MiddlewareContextBuilder::QUERY_TYPE]
            )
            && $context[MiddlewareContextBuilder::QUERY_TYPE] === MiddlewareContextBuilder::MANY;
    }
}