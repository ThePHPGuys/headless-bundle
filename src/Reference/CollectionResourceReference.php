<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


use Tpg\HeadlessBundle\Ast\Relation;
use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;

abstract class CollectionResourceReference implements ResourceReference
{
    public static function createFromAst(Relation $relation, $value):self
    {
        if($relation instanceof RelationToOne){
            return new RelationToOneReference($relation,$value);
        }

        if($relation instanceof RelationToMany){
            return new RelationToManyReference($relation,$value);
        }

        throw new \LogicException('Unknown relation');
    }
}