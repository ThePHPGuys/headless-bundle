<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Middleware;


use Tpg\HeadlessBundle\Filter\Filters;

final class FiltersContextBuilder
{
    public const FILTERS = self::class;

    use MiddlewareContextBuilderTrait;

    public function withFilters(Filters $filters):self
    {
        return $this->with(self::FILTERS,$filters);
    }
}