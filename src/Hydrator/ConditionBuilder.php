<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Hydrator;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Schema\Relation;

interface ConditionBuilder
{
    public const HIDDEN_OWN_ID = '___ownId';
    public const HIDDEN_RELATED_ID = '___relId';

    public function build(Relation $relation, QueryBuilder $queryBuilder, array $values):QueryBuilder;
}