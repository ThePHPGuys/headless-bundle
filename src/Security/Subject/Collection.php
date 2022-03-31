<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


final class Collection implements Subject
{
    use CollectionTrait;

    public function __construct(string $collection){
        $this->collection = $collection;
    }


}