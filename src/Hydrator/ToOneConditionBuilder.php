<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Hydrator;


use Doctrine\ORM\QueryBuilder;
use Tpg\HeadlessBundle\Schema\Relation;
use Tpg\HeadlessBundle\Service\SchemaService;

final class ToOneConditionBuilder implements ConditionBuilder
{
    private SchemaService $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    public function build(Relation $relation, QueryBuilder $queryBuilder, array $values):QueryBuilder
    {
        if(!$relation->isToOne()){
            throw new \LogicException('Incorrect cardinality');
        }

        $builder = clone $queryBuilder;
        return $builder
            ->addSelect([
                sprintf("%s %s", $this->getIdField($relation->relatedCollection), self::HIDDEN_OWN_ID),
                sprintf("%s %s", $this->getIdField($relation->relatedCollection), self::HIDDEN_RELATED_ID)
            ])
            ->where(sprintf('%s IN (:ownId)', self::HIDDEN_OWN_ID))
            ->setParameter(':ownId',$values);
    }

    private function getIdField(string $collectionName):string
    {
        return sprintf("%s.%s",
            $collectionName,
            $this->schemaService->getIdentifier($collectionName)->fieldName
        );
    }
}