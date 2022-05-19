<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query;


use Tpg\HeadlessBundle\Query\Sort\Direction;
use Tpg\HeadlessBundle\Query\Sort\Order;

final class Sort implements \IteratorAggregate
{
    /**
     * @var Order[]
     */
    private array $orders=[];

    private function __construct()
    {
    }

    public function addOrder(Order $order):void
    {
        $this->orders[] = $order;
    }

    public function getOrderFor(string $property):Order
    {
        $order = array_filter($this->orders,static fn(Order $o)=>$o->property()===$property);

        if(!isset($order[0])){
            throw new \InvalidArgumentException('No order for property');
        }

        return $order[0];
    }

    public function hasOrderFor(string $property):bool
    {
        $order = array_filter($this->orders,static fn(Order $o)=>$o->property()===$property);

        return isset($order[0]);
    }

    public function removeOrder(Order $order):void
    {
        array_splice(
            $this->orders,
            array_search($order,$this->orders,true),
            1
        );
    }

    public function isSorted():bool
    {
        return !empty($this->orders);
    }

    public static function unsorted():self
    {
        return new self();
    }

    public static function property(string $property, Direction $direction):self
    {
        return self::by(Order::by($property,$direction));
    }

    public static function asc(string $property):self
    {
        return self::by(Order::by($property,Direction::asc()));
    }

    public static function desc(string $property):self
    {
        return self::by(Order::by($property,Direction::desc()));
    }

    public static function by(Order ...$orders):self
    {
        $sort = new self();
        $sort->orders = $orders;
        return $sort;
    }

    /**
     * @return \ArrayIterator<Order>
     */
    public function getIterator(): \ArrayIterator
    {
        return new \ArrayIterator($this->orders);
    }


}