<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Service\SchemaService;
use Doctrine\DBAL\Query\QueryBuilder;
use Doctrine\ORM\Query\ResultSetMapping;

/**
 * Moved to NativeQuery because DQL doesn't support aliases with dots
 */
final class QueryWalkerDoctrine implements AstWalker
{
    private SchemaService $schemaService;
    private QueryBuilder $queryBuilder;
    private ResultSetMapping $resultSetMapping;
    private string $currentCollectionAlias;
    private string $currentCollection;

    public function __construct(
        SchemaService $schemaService,
        QueryBuilder $queryBuilder
    )
    {
        $this->schemaService = $schemaService;
        $this->queryBuilder = $queryBuilder;
    }


    public function visitCollection(Collection $collection):void
    {
        $collectionSchema = $this->schemaService->getCollection($collection->collectionName);

        $this->queryBuilder->from(
            $collectionSchema->table,
            "'{$collection->collectionName}'"
        );

        $this->currentCollectionAlias = $collection->collectionName;
        $this->currentCollection = $collection->collectionName;

        $relationChildren = [];
        //Traverse only fields
        foreach ($collection->children as $child){
            if(!($child instanceof Field)){
                $relationChildren[] = $child;
                continue;
            }
            $child->accept($this);
        }

        //Traverse relation
        foreach ($relationChildren as $child){
            if(!($child instanceof RelationToOne)){
                continue;
            }
            $child->accept($this);
        }

    }

    public function visitField(Field $field):void
    {
        $fieldMeta = $this->schemaService->getField($this->currentCollection,$field->fieldName);
        $this->addSelect(
            $this->getEscapedFieldAlias($fieldMeta->columnName),
            $this->getFieldAlias($fieldMeta->fieldName),
            $fieldMeta->type
        );
    }

    private function addSelect($column, $alias, $type='string')
    {
        $this->queryBuilder->addSelect("{$column} as '{$alias}'");
        $this->resultSetMapping->addScalarResult($column,$alias,$type);
    }

    private function getEscapedFieldAlias(string $fieldName):string
    {
        return '\''.$this->currentCollectionAlias.'\'.'.$fieldName;
    }

    private function getFieldAlias(string $fieldName):string
    {
        return $this->currentCollectionAlias.'.'.$fieldName;
    }

    public function visitRelationToOne(RelationToOne $relation):void
    {
        $relationMeta = $this->schemaService->getRelation($this->currentCollection,$relation->fieldName);

        //Single field requested
        if(!$relation->hasChildren()) {
            $this->addSelect(
                $this->getEscapedFieldAlias($relationMeta->joinColumn),
                $this->getFieldAlias($relation->fieldName),
                $this->schemaService->getField($relationMeta->collection, $relationMeta->referencedColumn)->type
            );
            return;
        }

        $relatedCollectionMeta = $this->schemaService->getCollection($relationMeta->collection);
        /** Save parent scope **/
        $parentAlias = $this->currentCollectionAlias;
        $parentCollection = $this->currentCollection;

        $this->currentCollectionAlias .= '.'.$relation->fieldName;
        $this->currentCollection = $relation->collectionName;

        $this->queryBuilder->join(
            "'{$parentAlias}'",
            $relatedCollectionMeta->table,
            "'{$this->currentCollectionAlias}'",
            "'{$parentAlias}'.{$relationMeta->joinColumn} = '{$this->currentCollectionAlias}'.{$relationMeta->referencedColumn}"
        );
        array_map(fn(Node $children)=>$children->accept($this),$relation->children);

        /** Restore parent scope **/
        $this->currentCollectionAlias = $parentAlias;
        $this->currentCollection = $parentCollection;

    }

    public function getQueryBuilder(Node $collection){
        $this->reset();
        $collection->accept($this);
        return $this->queryBuilder;
    }

    public function getResultSetMapping():ResultSetMapping{
        return $this->resultSetMapping;
    }

    private function reset():void
    {
        $this->currentCollectionAlias = '';
        $this->resultSetMapping = new ResultSetMapping();
    }

}