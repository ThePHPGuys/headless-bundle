<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Doctrine\ORM\QueryBuilder;

interface ExecutorOrmExtension
{
    public const TAG = 'headless.executor.orm.extension';
    
    public function supports(string $collection, QueryBuilder $queryBuilder, array $context=[]):bool;
    public function apply(string $collection, QueryBuilder $queryBuilder, array $context=[]);
}