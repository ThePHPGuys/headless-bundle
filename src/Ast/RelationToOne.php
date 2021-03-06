<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class RelationToOne extends Relation
{
    public function accept(AstWalker $walker)
    {
        return $walker->visitRelationToOne($this);
    }
}