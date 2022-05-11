<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Closure;
use Doctrine\ORM\QueryBuilder;

interface Stack
{
    public function handle(QueryBuilder $queryBuilder, array $context):array;
}