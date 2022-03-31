<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Schema;


final class Field
{

    public string $fieldName;
    public string $columnName;
    public string $type;
    public bool $nullable=true;

    public function __construct(string $fieldName, string $columnName=null, string $type='string', bool $nullable = true)
    {
        $this->fieldName = $fieldName;
        $this->columnName = $columnName??$fieldName;
        $this->type = $type;
        $this->nullable = $nullable;
    }
}