<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query;


abstract class Pageable
{
    abstract public function getPageNumber():int;
    abstract public function getPageSize():int;
    abstract public function getSort():Sort;

    public function isPaged():bool
    {
        return true;
    }

    public static function unpaged():self
    {
        return new Unpaged();
    }

    public function getSortOr(Sort $sort):Sort
    {
        return $this->getSort()->isSorted()?$this->getSort():$sort;
    }

}