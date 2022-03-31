<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class Collection extends Node
{
    public string $collectionName;

    public static function create(string $collectionName):self
    {
        $s = new self();
        $s->collectionName = $collectionName;
        return $s;
    }

    public function accept(AstWalker $walker):void
    {
        $walker->visitCollection($this);
    }


}