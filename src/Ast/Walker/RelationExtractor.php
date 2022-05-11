<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;

use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;

final class RelationExtractor implements AstWalker
{
    private array $relations=[];

    public function visitCollection(Collection $collection):void
    {
        array_map(fn(Node $node)=>$node->accept($this),$collection->children);
    }

    public function visitField(Field $field)
    {
        //Just skip fields
    }

    public function visitRelationToOne(RelationToOne $relation):void
    {
        $this->relations[$relation->fieldName] = $relation;
    }

    public function visitRelationToMany(RelationToMany $relation):void
    {
        $this->relations[$relation->fieldName] = $relation;
    }

    public function extract(Collection $collection):array
    {
        $this->relations = [];
        $collection->accept($this);
        return $this->relations;
    }

}