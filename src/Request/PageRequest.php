<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Query\Sort;

final class PageRequest extends Pageable
{
    private int $page;
    private int $size;
    private Sort $sort;

    /**
     * Pages are zero indexed, thus providing 0 for $page will return the first page.
     * @param  int  $page
     * @param  int  $size
     * @param  Sort  $sort
     */
    public function __construct(int $page, int $size, Sort $sort)
    {
        if ($page < 0) {
            throw new \InvalidArgumentException("Page index must not be less than zero!");
        }

        if ($size < 1) {
            throw new \InvalidArgumentException("Page size must not be less than one!");
        }

        $this->page = $page;
        $this->size = $size;
        $this->sort = $sort;
    }
    public function getPageNumber(): int
    {
        return $this->page;
    }

    public function getPageSize(): int
    {
        return $this->size;
    }

    public function getSort(): Sort
    {
        return $this->sort;
    }

    public function withSort(Sort $sort):self
    {
        return new static($this->page,$this->size,$sort);
    }

    public static function of(int $page, int $size, Sort $sort=null):self
    {
        return new self($page,$size,$sort?:Sort::unsorted());
    }
}