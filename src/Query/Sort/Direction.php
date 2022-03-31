<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query\Sort;

/**
 * TODO: Move to Enum on php 8.1
 */
final class Direction
{
    private const ASC='ASC';
    private const DESC='DESC';

    private string $direction;

    private function __construct(string $direction)
    {
        if(!in_array(strtoupper($direction),[self::ASC,self::DESC])){
            throw new \InvalidArgumentException('Incorrect direction');
        }
        $this->direction = $direction;
    }

    /**
     * Returns whether the direction is ascending.
     * @return bool
     */
    public function isAscending():bool
    {
        return $this->direction === self::ASC;
    }

    /**
     *  Returns whether the direction is descending.
     * @return bool
     */
    public function isDescending():bool
    {
        return $this->direction === self::ASC;
    }

    public static function asc():self
    {
        return new self(self::ASC);
    }

    public static function desc():self
    {
        return new self(self::DESC);
    }

    public static function fromString(string $direction):self
    {
        return new self($direction);
    }

    public function __toString()
    {
        return $this->direction;
    }
}