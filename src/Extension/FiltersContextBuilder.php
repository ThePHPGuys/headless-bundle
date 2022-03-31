<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Tpg\HeadlessBundle\Extension\Filter\Filters;

final class FiltersContextBuilder
{
    use ExtensionContextBuilderTrait;

    public function withFilters(Filters $filters):self
    {
        return $this->with(FiltersExtension::CONTEXT_KEY,$filters);
    }
}