<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Tpg\HeadlessBundle\Query\Pageable;

final class PageableContextBuilder
{
    use MiddlewareContextBuilderTrait;

    public const PAGEABLE = 'pageable';

    public function withPageable(Pageable $pageable): self
    {
        return $this->with(self::PAGEABLE,$pageable);
    }
}