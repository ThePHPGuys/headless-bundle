<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class Collection extends Node
{
    public string $name;

    public static function create(string $name):self
    {
        $s = new self();
        $s->name = $name;
        return $s;
    }

    public function accept(AstWalker $walker)
    {
        return $walker->visitCollection($this);
    }


}