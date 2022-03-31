<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query;


final class Unpaged extends Pageable
{
    public function isPaged(): bool
    {
        return false;
    }

    public function getPageNumber(): int
    {
        throw new \LogicException('Unsupported operation');
    }

    public function getPageSize(): int
    {
        throw new \LogicException('Unsupported operation');
    }

    public function getSort(): Sort
    {
        return Sort::unsorted();
    }

}