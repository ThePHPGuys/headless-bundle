<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


final class Item implements Subject
{
    use CollectionTrait;
    public object $item;

    public function __construct(string $collection, object $item){
        $this->collection = $collection;
        $this->item = $item;
    }
}