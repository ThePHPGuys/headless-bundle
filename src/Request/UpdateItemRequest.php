<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


final class UpdateItemRequest implements ModifyItemRequest
{
    private array $data;

    private function __construct(array $rawData)
    {
        $this->data = $rawData;
    }

    public function getData():array
    {
        return $this->data;
    }

    public static function create(array $data): self
    {
        return new self($data);
    }
}