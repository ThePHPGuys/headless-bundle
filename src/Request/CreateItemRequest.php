<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;

/*
 * Move to
 * ItemModifyRequest
 * Add operation and collection key into route
 * Add request validation
 * Add methods to raw data
 * Add methods for validation violations
 */

final class CreateItemRequest implements ModifyItemRequest
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

    public static function create(array $data): ModifyItemRequest
    {
        return new self($data);
    }


}