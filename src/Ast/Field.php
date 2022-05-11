<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


final class Field extends Node
{
    public string $fieldName;

    public static function create(string $fieldName):self
    {
        $s = new self();
        $s->fieldName = $fieldName;
        return  $s;
    }

    public function accept(AstWalker $walker)
    {
        return $walker->visitField($this);
    }
}