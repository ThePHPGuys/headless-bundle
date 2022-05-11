<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


interface AstWalker
{
    public function visitCollection(Collection $collection);
    public function visitField(Field $field);
    public function visitRelationToOne(RelationToOne $relation);
    public function visitRelationToMany(RelationToMany $relation);
}