<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query\Sort;


final class Order
{
    private string $property;
    private Direction $direction;

    private function __construct(string $property,Direction $direction)
    {
        $this->property = $property;
        $this->direction = $direction;
    }

    public function direction():Direction
    {
        return $this->direction;
    }

    public function property():string
    {
        return $this->property;
    }

    public static function asc(string $property):self
    {
        return new self($property,Direction::asc());
    }

    public static function desc(string $property):self
    {
        return new self($property,Direction::desc());
    }

    public static function by(string $property, Direction $direction):self
    {
        return new self($property,$direction);
    }

}