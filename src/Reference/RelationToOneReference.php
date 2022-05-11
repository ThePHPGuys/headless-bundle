<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


use Tpg\HeadlessBundle\Ast\RelationToMany;
use Tpg\HeadlessBundle\Ast\RelationToOne;
use Tpg\HeadlessBundle\Ast\Walker\FieldNamesExtractor;

final class RelationToOneReference extends CollectionResourceReference
{
    public RelationToOne $relation;
    public string $value;
    private array $fields;

    public function __construct(RelationToOne $relation, string $value)
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