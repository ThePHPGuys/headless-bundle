<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Tpg\HeadlessBundle\Security\Checker;
use Tpg\HeadlessBundle\Security\Subject\Subject;

final class SecurityChecker implements Checker
{
    public function isGranted($operation, Subject $subject): bool
    {
        return true;
    }

}