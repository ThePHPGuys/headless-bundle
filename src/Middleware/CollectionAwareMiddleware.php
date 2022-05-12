<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Tpg\HeadlessBundle\Ast\Collection;

interface CollectionAwareMiddleware
{
    public function setCollection(Collection $collection):void;
    public function getCollection():Collection;
}