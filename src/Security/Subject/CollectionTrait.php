<?php

namespace Tpg\HeadlessBundle\Security\Subject;

trait CollectionTrait
{
    private string $collection;

    public function collection():string
    {
        return $this->collection;
    }
}