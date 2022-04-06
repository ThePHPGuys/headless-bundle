<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


interface AccessOperation
{
    public const READ='read';
    public const CREATE='write';
    public const UPDATE='update';
    public const DELETE='delete';
    public const SORT='sort';
    public const FILTER='filter';
}