<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Schema;


interface Schema
{
    /**
     * @return string[]
     */
    public function getCollections():array;
    public function getCollection(string $collection):Collection;
    public function hasCollection(string $collection):bool;

    /**
     * @return string[]
     */
    public function getNonRelationFields(string $collection):array;
    public function getField(string $collection, string $fieldName):Field;
    public function getIdentifier(string $collection):Field;

    /**
     * @return string[]
     */
    public function getRelationFields(string $collection, int $type=null):array;
    public function getRelation(string $collection,string $relation):Relation;
}