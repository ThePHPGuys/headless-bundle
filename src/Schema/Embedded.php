<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Schema;


final class Embedded
{

    public string $class;

    public function __construct(string $class)
    {

        $this->class = $class;
    }
}