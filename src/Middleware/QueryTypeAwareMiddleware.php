<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


interface QueryTypeAwareMiddleware
{
    public function setQueryType(string $type);
    public function getQueryType():string;
}