<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


interface Subject
{
    public function collection():string;
}