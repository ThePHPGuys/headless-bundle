<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Query\Sort;
use Tpg\HeadlessBundle\Query\Sort\Order;
use Tpg\HeadlessBundle\Security\Subject\AccessOperation;
use Tpg\HeadlessBundle\Service\AstFactory;
use Tpg\HeadlessBundle\Service\ExecutorORM;
use Doctrine\Common\Collections\Criteria;
use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Service\SchemaService;
use Tpg\HeadlessBundle\Service\SecuredAstFactory;

final class PageableExtension implements ExecutorOrmExtension
{
    public const CONTEXT_KEY = self::class;
    private int $maxPageSize;
    private SecuredAstFactory $astFactory;

    public function __construct(SecuredAstFactory $astFactory, $maxPageSize = 50)
    {
        $this->maxPageSize = $maxPageSize;
        $this->astFactory = $astFactory;
    }

    public function supports(string $collection, QueryBuilder $queryBuilder, array $context = []): bool
    {
        return array_key_exists(self::CONTEXT_KEY, $context) && $context[ExecutorOrmExtension::OPERATION_CONTEXT_KEY]===ExecutorOrmExtension::OPERATION_GET_MANY;
    }

    public function apply(string $collection, QueryBuilder $queryBuilder, array $context = [])
    {
        /** @var Pageable $pageable */
        $pageable = $context[self::CONTEXT_KEY];
        $this->addPagination($queryBuilder,$pageable->getPageSize(),$pageable->getPageNumber());
        $this->addSorting($collection, $queryBuilder, $pageable->getSortOr(Sort::unsorted()));
    }

    private function addPagination(QueryBuilder $queryBuilder, int $pageSize, int $pageNumber):void
    {
        $maxPageSize = min($pageSize,$this->maxPageSize);
        $queryBuilder->setFirstResult($maxPageSize*$pageNumber);
        $queryBuilder->setMaxResults($maxPageSize);
    }

    private function addSorting(string $collection, QueryBuilder $queryBuilder,Sort $sort):void
    {
        if(!$sort->isSorted()){
            return;
        }

        foreach ($sort as $order){
            $this->addOrder($collection, $queryBuilder, $order);
        }
        //dd($queryBuilder->getDQL());
    }

    private function addOrder(string $collection, QueryBuilder $queryBuilder, Order $order)
    {
        $collectionAst = $this->astFactory->createCollectionAstFromFields($collection, new Fields([$order->property()]),AccessOperation::SORT);
        $walker = new PageableSortAstWalker($queryBuilder,$order->direction());
        $walker->addJoins($collectionAst);
    }
}