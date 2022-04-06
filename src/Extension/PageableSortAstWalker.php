<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Query\Sort\Direction;
use Tpg\HeadlessBundle\Service\SchemaService;

final class PageableSortAstWalker implements AstWalker
{
    private QueryBuilder $queryBuilder;
    private string $currentCollectionAlias;
    private Direction $direction;

    public function __construct(QueryBuilder $queryBuilder, Direction $direction)
    {
        $this->queryBuilder = $queryBuilder;
        $this->direction = $direction;
    }

    public function visitCollection(Collection $collection)
    {
        $this->currentCollectionAlias = $collection->collectionName;
        array_map(fn($child)=>$child->accept($this),$collection->children);
    }

    public function visitField(Field $field)
    {
        $this->queryBuilder->addOrderBy(
            sprintf('%s.%s',$this->currentCollectionAlias,$field->fieldName),
            $this->direction->isAscending()?'ASC':'DESC'
        );
    }

    public function visitRelationToOne(RelationToOne $relation)
    {
        $alias = sprintf('_sort_join_%s',$relation->collectionName);

        if(!in_array($alias,$this->queryBuilder->getAllAliases(),true)) {
            //Add join if not exists
            $this->queryBuilder->leftJoin(
                sprintf('%s.%s', $this->currentCollectionAlias, $relation->fieldName),
                $alias
            );
        }

        $this->currentCollectionAlias = $alias;
        array_map(fn($child)=>$child->accept($this),$relation->children);
    }

    public function addJoins(Collection $collection){
        $collection->accept($this);
    }
}