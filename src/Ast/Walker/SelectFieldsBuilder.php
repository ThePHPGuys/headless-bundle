<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\Relation;
use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Reference\RelationToOneReference;
use Tpg\HeadlessBundle\Service\SchemaService;
use Doctrine\ORM\QueryBuilder;

final class SelectFieldsBuilder implements AstWalker
{
    private SchemaService $schemaService;
    private QueryBuilder $queryBuilder;
    private Collection $parentCollection;

    public function __construct(
        SchemaService $schemaService
    )
    {
        $this->schemaService = $schemaService;
    }

    public function visitCollection(Collection $collection)
    {
        $this->parentCollection = $collection;
        array_map(fn(Node $node)=>$node->accept($this),$collection->children);
    }


    public function visitField(Field $field)
    {
        $this->queryBuilder->addSelect(
            sprintf('%s.%s',$this->parentCollection->name,$field->fieldName)
        );
    }

    public function visitRelationToOne(RelationToOne $relation)
    {
        $this->queryBuilder->addSelect(sprintf(
            "IDENTITY(%s.%s) as %s",
            $this->parentCollection->name,
            $relation->fieldName,
            $relation->fieldName
        ));
    }


    public function visitRelationToMany(RelationToMany $relation)
    {
        $identifier = $this->schemaService->getIdentifier($this->parentCollection->name);
        $this->queryBuilder->addSelect(
            sprintf('%s.%s %s',
                $this->parentCollection->name,
                $identifier->fieldName,
                $relation->fieldName
            )
        );
    }


    public function build(QueryBuilder $queryBuilder, Collection $collection):QueryBuilder
    {
        $this->queryBuilder = clone $queryBuilder;
        $this->parentCollection = $collection;
        $collection->accept($this);
        return $this->queryBuilder;
    }





}