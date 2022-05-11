<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Relation;
use Tpg\HeadlessBundle\Ast\Walker\RelationExtractor;
use Tpg\HeadlessBundle\Reference\CollectionResourceReference;

final class AttachReferencesToResultMiddleware implements Middleware
{
    /**
     * @param  QueryBuilder  $queryBuilder
     * @param  array  $context
     * @param  Stack  $stack
     * @return array
     */
    public function process(QueryBuilder $queryBuilder, array $context, Stack $stack): array
    {
        $result = $stack->handle($queryBuilder,$context);
        if(!isset($context[MiddlewareContextBuilder::COLLECTION])){
            return $result;
        }
        /** @var Collection $collection */
        $collection = $context[MiddlewareContextBuilder::COLLECTION];
        $relations = $this->extractRelations($collection);
        return $this->attachReferencesToResult($result,$relations);
    }

    /**
     * @return array<string,Relation>
     */
    private function extractRelations(Collection $collection):array
    {
        return (new RelationExtractor())->extract($collection);
    }

    /**
     * @param  array  $data
     * @param  array<string,Relation>  $relations
     * @return array
     */
    private function attachReferencesToResult(array $data, array $relations):array
    {
        return array_map(fn(array $row)=>$this->attachReferencesToRow($row,$relations),$data);
    }

    /**
     * @param  array  $row
     * @param  array<string,Relation>  $relations
     * @return array
     */
    private function attachReferencesToRow(array $row, array $relations):array
    {
        foreach ($row as $field => $value){
            if(!array_key_exists($field,$relations)) {
                continue;
            }
            $row[$field] = CollectionResourceReference::createFromAst($relations[$field], (string)$value);
        }

        return $row;
    }
}