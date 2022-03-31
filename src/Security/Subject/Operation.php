<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Security\Subject;


interface Operation
{
    public const READ='read';
    public const CREATE='write';
    public const UPDATE='update';
    public const DELETE='delete';
}