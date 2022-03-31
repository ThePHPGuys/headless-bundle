<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Service\SchemaService;
use Doctrine\ORM\QueryBuilder;

final class CollectionToORMQueryBuilder implements AstWalker
{
    private SchemaService $schemaService;
    private QueryBuilder $queryBuilder;
    private Collection $collection;
    private array $deferredRelations=[];

    public function __construct(
        SchemaService $schemaService,
        QueryBuilder $initialQueryBuilder,
        Collection $collection
    )
    {
        $this->queryBuilder = clone $initialQueryBuilder;
        $this->schemaService = $schemaService;
        $this->collection = $collection;
        $collection->accept($this);
    }

    public function visitCollection(Collection $collection)
    {
        $this->collection = $collection;
        $collectionMeta = $this->schemaService->getCollection($collection->collectionName);
        $this->queryBuilder->from(
            $collectionMeta->class,
            $collection->collectionName );

        $relationChildren = [];

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


    public function visitField(Field $field)
    {
        $this->queryBuilder->addSelect(
            sprintf('%s.%s',$this->collection->collectionName,$field->fieldName)
        );
    }

    ////For toMany current collection id shouldbe selected as relation name
    public function visitRelationToOne(RelationToOne $relation)
    {

        $this->queryBuilder->addSelect(
            sprintf('IDENTITY(%s.%s) %s',
                $this->collection->collectionName,
                $relation->fieldName,
                $relation->fieldName
            )
        );
        if(count($relation->children)>0){
            $this->deferredRelations[$relation->fieldName] = $relation;
        }
    }

    public function getBuilder():QueryBuilder
    {
        return $this->queryBuilder;
    }

    public function getDeferredRelations():array
    {
        return $this->deferredRelations;
    }


}