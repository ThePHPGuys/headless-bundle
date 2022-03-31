<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security;


use Tpg\HeadlessBundle\Security\Subject\Subject;

interface Checker
{
    public function isGranted($operation, Subject $subject):bool;
}