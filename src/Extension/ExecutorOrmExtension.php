<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Doctrine\ORM\QueryBuilder;

interface ExecutorOrmExtension
{
    public const TAG = 'headless.executor.orm.extension';
    public const OPERATION_CONTEXT_KEY = 'operation';
    public const OPERATION_COUNT = 'count';
    public const OPERATION_GET_MANY = 'many';
    public const OPERATION_GET_ONE = 'one';

    public function supports(string $collection, QueryBuilder $queryBuilder, array $context=[]):bool;
    public function apply(string $collection, QueryBuilder $queryBuilder, array $context=[]);
}