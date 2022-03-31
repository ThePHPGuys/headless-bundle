<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Schema;


final class Collection
{
    public string $table;
    /**
     * @var class-string
     */
    public string $class;

    /**
     * @param  class-string  $class
     */
    public function __construct(string $table, string $class){
        $this->table = $table;
        $this->class = $class;
    }
}