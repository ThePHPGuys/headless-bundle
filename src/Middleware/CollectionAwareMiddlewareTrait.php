<?php

namespace Tpg\HeadlessBundle\Middleware;

use Tpg\HeadlessBundle\Ast\Collection;

trait CollectionAwareMiddlewareTrait
{
    private Collection $collection;

    public function setCollection(Collection $collection):void
    {
        $this->collection = $collection;
    }

    public function getCollection():Collection
    {
        return $this->collection;
    }
}