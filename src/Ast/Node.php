<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


abstract class Node
{
    /**
     * @var Node[]
     */
    public array $children=[];

    public function hasChildren():bool
    {
        return (bool)$this->children;
    }

    abstract public function accept(AstWalker $walker);
}