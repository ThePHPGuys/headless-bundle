<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


final class Field implements Subject
{
    use CollectionTrait;
    public string $name;

    public function __construct(string $collection, string $name){
        $this->collection = $collection;
        $this->name = $name;
    }

}