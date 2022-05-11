<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class RelationToMany extends Relation
{
    public function accept(AstWalker $walker)
    {
        return $walker->visitRelationToMany($this);
    }
}