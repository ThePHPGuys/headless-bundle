<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Walker\SecurityWalker;
use Tpg\HeadlessBundle\Security\Checker;
use Tpg\HeadlessBundle\Security\Subject\Collection as CollectionSubject;
use Tpg\HeadlessBundle\Security\Subject\Field as FieldSubject;
use Tpg\HeadlessBundle\Security\Subject\Item as ItemSubject;

final class SecurityService
{
    private SchemaService $schemaService;
    private Checker $securityChecker;

    public function __construct(SchemaService $schemaService, Checker $securityChecker)
    {
        $this->schemaService = $schemaService;
        $this->securityChecker = $securityChecker;
    }

    public function filterEntityData(string $collection, array $data, string $operation):array
    {
        if(!$this->isCollectionGranted($collection,$operation)){
            return [];
        }
        $fields = $this->schemaService->getFields($collection);
        $relations = $this->schemaService->getRelationFields($collection);

        foreach ($data as $field=>$value){
            if(!in_array($field,$fields,true)){
                continue;
            }
            if(!$this->isFieldGranted($collection,$field,$operation)){
                unset($data[$field]);
                continue;
            }

            if(is_array($value) && in_array($field,$relations,true)){
                $relation = $this->schemaService->getRelation($collection,$field);
                $data[$field] = $this->filterEntityData($relation->collection,$value,$operation);
            }
        }
        return $data;
    }

    public function filterAst(Collection $node):Collection
    {
        return (new SecurityWalker($this->securityChecker))->getEffective($node);
    }

    public function isFieldGranted(string $collection, string $field, string $operation):bool
    {
        return $this->securityChecker->isGranted($operation, new FieldSubject($collection,$field));
    }

    public function isCollectionGranted(string $collection, string $operation):bool
    {
        return $this->securityChecker->isGranted($operation, new CollectionSubject($collection));
    }

    public function isItemGranted(string $collection, object $item, string $operation):bool
    {
        return $this->securityChecker->isGranted($operation, new ItemSubject($collection,$item));
    }
}