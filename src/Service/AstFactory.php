<?php
declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;

use Tpg\HeadlessBundle\Ast\Collection;
use Tpg\HeadlessBundle\Ast\Embedded;
use Tpg\HeadlessBundle\Ast\Field;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Query\Fields;
use function str_starts_with;

class AstFactory
{

    private SchemaService $schemaService;

    public function __construct(SchemaService $schemaService)
    {
        $this->schemaService = $schemaService;
    }

    public function fromFields(string $collection, Fields $query):Collection
    {
        $ast = Collection::create($collection);
        $ast->children = $this->parseFields($collection, $query->fields());
        return $ast;
    }


    private function parseFields(string $collection, array $fields, array $allowedFields = ['*']): array
    {
        $fields = $this->convertWildcards($collection, $fields, $allowedFields);

        if (!$fields) {
            return [];
        }

        $children = [];
        $relationalStructure = [];
        foreach ($fields as $field) {
            $isRelation = strpos($field, '.') !== false || $this->schemaService->hasRelation($collection, $field);

            if (!$isRelation) {
                $children[] = Field::create($field);
                continue;
            }

            //Relation related code
            $parts = explode('.',$field);
            $rootField = $parts[0];

            if(!array_key_exists($rootField,$relationalStructure)){
                $relationalStructure[$rootField] = [];
            }

            if(count($parts)>1){
                $childKey = implode('.',array_slice($parts,1));
                $relationalStructure[$rootField][] = $childKey;
            }
        }

        foreach ($relationalStructure as $fieldKey=>$nestedFields){

            if($this->schemaService->hasRelation($collection,$fieldKey)) {
                $relation = $this->schemaService->getRelation($collection, $fieldKey);
                if ($relation->isToOne()) {
                    $child = RelationToOne::create($fieldKey, $relation->collection);
                    $child->children = $this->parseFields($relation->collection, $nestedFields);
                    $children[] = $child;
                }
            }
        }
        return $children;
    }

    private function convertWildcards(string $collection, array $fields, array $allowedFields = ['*']): array
    {
        if(!$this->schemaService->hasCollection($collection)){
            return [];
        }
        $fieldsInCollection = $this->schemaService->getFields($collection);

        if (!$fields || !$allowedFields) {
            return [];
        }

        if (in_array('*', $allowedFields, true)) {
            $allowedFields = $fieldsInCollection;
        }

        foreach ($fields as $index => $fieldKey) {
            if (strpos($fieldKey, '*') === false) {
                continue;
            }

            if ($fieldKey === '*') {
                array_splice($fields, $index, 1, $allowedFields);
                continue;
            }


            if (str_starts_with($fieldKey, '*.')) {
                $parts = explode('.', $fieldKey);

                $relationFields = array_filter(
                    $this->schemaService->getRelationFields($collection),
                    static fn($field) => in_array($field, $allowedFields, true)
                );
                $nonRelationalFields = array_filter(
                    $allowedFields,
                    static fn($field) => !in_array($field, $relationFields, true)
                );
                $fieldPostfixParts = $parts;
                array_shift($fieldPostfixParts);
                array_splice(
                    $fields,
                    $index,
                    1,
                    [
                        ...$nonRelationalFields,
                        ...array_map(static fn(string $field) => implode('.', [$field, ...$fieldPostfixParts]),
                            $relationFields)

                    ]
                );
            }

        }
        return $fields;
    }
}