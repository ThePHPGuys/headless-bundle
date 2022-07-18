<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Hydrator;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Schema\Relation;
use Tpg\HeadlessBundle\Service\SchemaService;

final class ToManyConditionBuilder implements ConditionBuilder
{
    private SchemaService $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    public function build(Relation $relation, QueryBuilder $queryBuilder, array $values):QueryBuilder
    {
        if(!$relation->isToMany()){
            throw new \LogicException('Incorrect cardinality');
        }

        $builder = clone $queryBuilder;
        $ownIdField = $this->getIdField($relation->collection);
        $relatedIdField = $this->getIdField($relation->relatedCollection);
        $builder
            ->addSelect([
                sprintf("%s %s", $ownIdField, self::HIDDEN_OWN_ID ),
                sprintf("%s %s", $relatedIdField, self::HIDDEN_RELATED_ID)
            ])

            ->join(sprintf('%s.%s',$relation->collection,$relation->name),$relation->relatedCollection)
            ->where(sprintf('%s IN (:ownId)', $ownIdField))->setParameter(':ownId',$values);

        return $builder;
    }

    private function getIdField(string $collectionName):string
    {
        return sprintf("%s.%s",
            $collectionName,
            $this->schemaService->getIdentifier($collectionName)->fieldName
        );
    }
}