<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;

final class FieldNamesExtractor implements AstWalker
{
    public function visitCollection(Collection $collection)
    {
        return array_map(fn(Node $node)=>$node->accept($this),$collection->children);
    }

    public function visitField(Field $field)
    {
        return $field->fieldName;
    }

    public function visitRelationToOne(RelationToOne $relation)
    {
        return $relation->fieldName;
    }

    public function visitRelationToMany(RelationToMany $relation)
    {
        return $relation->fieldName;
    }

    public function extract(Collection $collection)
    {
        return $collection->accept($this);
    }

}