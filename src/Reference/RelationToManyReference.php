<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;

use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\Walker\FieldNamesExtractor;

final class RelationToManyReference extends CollectionResourceReference
{
    public RelationToMany $relation;
    public string $value;
    private array $fields;

    public function __construct(RelationToMany $relation, string $value)
    {
        $this->value = $value;
        $this->relation = $relation;
        $this->fields = (new FieldNamesExtractor())->extract($relation->toCollection());
        sort($this->fields);
    }

    public function type(): string
    {
        return sprintf("%s.%s:%s",$this->relation->collection,$this->relation->fieldName,implode(',',$this->fields));
    }
}