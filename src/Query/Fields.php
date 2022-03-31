<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Query;


final class Fields
{
    /**
     * @var string[]
     */
    private array $fields;

    /**
     * @param  string[]  $fields
     */
    public function __construct(array $fields=['*']){

        $this->fields = $fields;
    }

    public function fields():array
    {
        return $this->fields;
    }
}