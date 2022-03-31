<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class RelationToOne extends Node
{
    public string $fieldName;
    public string $collectionName;

    public static function create(string $fieldName, string $collection):self
    {
        $s = new self();
        $s->collectionName = $collection;
        $s->fieldName = $fieldName;
        return $s;
    }

    public function accept(AstWalker $walker):void
    {
        $walker->visitRelationToOne($this);
    }
}