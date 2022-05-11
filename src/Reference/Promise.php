<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


final class Promise implements \JsonSerializable
{
    private $data;

    public function resolve($data):void
    {
        $this->data = $data;
    }

    public function jsonSerialize()
    {
        return $this->data;
    }

}