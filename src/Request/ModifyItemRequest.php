<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


interface ModifyItemRequest
{
    public function getData():array;
    public static function create(array $data):self;
}