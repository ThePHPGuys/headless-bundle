<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;

final class StripToManyRelations implements AstWalker
{
    public function visitCollection(Collection $collection):void
    {
        $this->walkChildren($collection);
    }

    public function visitField(Field $field):void
    {
        // just ignore
    }

    public function visitRelationToOne(RelationToOne $relation):void
    {
        $this->walkChildren($relation);
    }

    public function visitRelationToMany(RelationToMany $relation):void
    {
        throw new \Exception('Should not be reached');
    }

    public static function strip(Collection $collection):void
    {
        $collection->accept(new self());
    }

    private function walkChildren(Node $childrenNode):void
    {
        foreach ($childrenNode->children as $cId=>$child){
            if($child instanceof RelationToMany){
                array_splice($childrenNode->children,$cId,1);
                continue;
            }
            $child->accept($this);
        }
    }
}