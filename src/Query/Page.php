<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query;
/**
 * @template T
 */
final class Page implements \IteratorAggregate
{
    private iterable $content;
    private Pageable $pageable;
    private int $total;

    public function __construct(iterable $content, Pageable $pageable, int $total = 0)
    {
        $this->content = $content;
        $this->pageable = $pageable;
        $this->total = $total;
    }

    public function getSize():int
    {
        return $this->pageable->getPageSize();
    }

    public function getNumber():int
    {
        return $this->pageable->getPageNumber();
    }

    public function getTotalElements():int
    {
        return $this->total;
    }

    public function getTotalPages():int
    {
        return $this->total===0?1:(int)ceil($this->total/$this->getSize());
    }

    /**
     * @return iterable<T>
     */
    public function getIterator():iterable
    {
        yield from $this->content;
    }

}