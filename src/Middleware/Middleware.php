<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\ORM\QueryBuilder;

interface Middleware
{
    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack):array;
}