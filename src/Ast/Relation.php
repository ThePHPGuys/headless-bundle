<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast;


abstract class Relation extends Node
{
    public string $fieldName;
    public string $collection;
    public string $relatedCollection;

    public static function create(string $collection, string $fieldName, string $relatedCollection):self
    {
        $s = new static();
        $s->collection = $collection;
        $s->relatedCollection = $relatedCollection;
        $s->fieldName = $fieldName;
        return $s;
    }

    public function toCollection():Collection
    {
        $collection = Collection::create($this->relatedCollection);
        $collection->children = $this->children;
        return $collection;
    }
}