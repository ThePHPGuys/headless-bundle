<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;


final class ItemPayload
{
    public string $collection;
    public array $payload;

    public function __construct(string $collection, array $payload)
    {
        $this->collection = $collection;
        $this->payload = $payload;
    }

}