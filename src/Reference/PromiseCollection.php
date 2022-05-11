<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


final class PromiseCollection
{
    /**
     * @var array{array{Promise,ResourceReference}}
     */
    private array $promises = [];

    public function remember(ResourceReference $placeholder): Promise
    {
        $promise = new Promise();
        $this->promises[] = [$promise, $placeholder];

        return $promise;
    }

    public function release()
    {
        [$promises, $this->promises] = [$this->promises, []];

        return $promises;
    }
}