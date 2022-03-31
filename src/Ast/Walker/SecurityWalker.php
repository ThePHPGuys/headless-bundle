<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Ast\Walker;


use Tpg\HeadlessBundle\Ast\AstWalker;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\Node;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Security\Checker;
use Tpg\HeadlessBundle\Security\Subject\Collection as CollectionSubject;
use Tpg\HeadlessBundle\Security\Subject\Field as FieldSubject;
use Tpg\HeadlessBundle\Security\Subject\Operation;

final class SecurityWalker implements AstWalker
{
    private Checker $checker;

    public function __construct(Checker $checker)
    {
        $this->checker = $checker;
    }

    private function isField(Node $node):bool
    {
        return $node instanceof Field;
    }

    private function isRelationToOne(Node $node):bool
    {
        return $node instanceof RelationToOne;
    }

    private function isFieldGranted(string $collection, string $field):bool
    {
        return $this->checker->isGranted(Operation::READ,
            new FieldSubject($collection,$field));
    }

    private function isCollectionGranted(string $collection):bool
    {
        return $this->checker->isGranted(Operation::READ,
            new CollectionSubject($collection));
    }

    /**
     * @psalm-pure
     * @param  string  $collectionName
     * @param  Node[]  $children
     * @return Node[]
     */
    private function walkChildren(string $collectionName, array $children):array
    {
        if(!$this->isCollectionGranted($collectionName)){
            return [];
        }

        foreach ($children as $cId=>$child){
            /** @var Field $child */
            if($this->isField($child) && $this->isFieldGranted($collectionName, $child->fieldName)) {
                continue;
            }

            /** @var RelationToOne $child */
            if($this->isRelationToOne($child) && $this->isFieldGranted($collectionName, $child->fieldName)){
                $child->accept($this);
                continue;
            }
            //Remove field
            array_splice($children,$cId,1);

        }
        return  $children;
    }

    public function visitCollection(Collection $collection):void
    {
        $collection->children = $this->walkChildren($collection->collectionName,$collection->children);
    }

    public function visitField(Field $field):void
    {
        throw new \LogicException('Should not be reached');
    }

    public function visitRelationToOne(RelationToOne $relation):void
    {
        $relation->children = $this->walkChildren($relation->collectionName,$relation->children);
    }

    public function getEffective(Collection $collection):Collection
    {
        $resultCollection = clone $collection;
        $resultCollection->accept($this);
        return $resultCollection;
    }

}