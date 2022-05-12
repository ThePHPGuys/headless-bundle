<?php

namespace Tpg\HeadlessBundle\Middleware;

trait QueryTypeAwareMiddlewareTrait
{
    private string $queryType;

    public function setQueryType(string $queryType):void
    {
        $this->queryType = $queryType;
    }

    public function getQueryType():string
    {
        return $this->queryType;
    }
}