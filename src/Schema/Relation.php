<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Schema;


final class Relation
{
    /**
    * Identifies a one-to-one association.
    */
    public const ONE_TO_ONE = 1;

    /**
     * Identifies a many-to-one association.
     */
    public const MANY_TO_ONE = 2;

    /**
     * Identifies a one-to-many association.
     */
    public const ONE_TO_MANY = 4;

    /**
     * Identifies a many-to-many association.
     */
    public const MANY_TO_MANY = 8;

    /**
     * Combined bitmask for to-one (single-valued) associations.
     */
    public const TO_ONE = 3;

    /**
     * Combined bitmask for to-many (collection-valued) associations.
     */
    public const TO_MANY = 12;

    public const ASSOCIATION = 1;
    public const COMPOSITION = 2;


    private int $cardinality;
    private int $type;
    public string $name;
    public string $collection;
    public string $joinColumn;
    public string $referencedColumn;

    public function __construct(string $name, int $cardinality, string $collection, int $type=self::ASSOCIATION)
    {
        $this->name = $name;
        $this->cardinality = $cardinality;
        $this->collection = $collection;
        $this->type = $type;
    }

    public function isToOne():bool
    {
        return (bool)($this->cardinality & self::TO_ONE);
    }

    public function isToMany():bool
    {
        return (bool)($this->cardinality & self::TO_MANY);
    }

    public function isAssociation():bool
    {
        return $this->type === self::ASSOCIATION;
    }

    public function isComposition():bool
    {
        return $this->type === self::COMPOSITION;
    }

    public function cardinality():int
    {
        return $this->cardinality;
    }

}