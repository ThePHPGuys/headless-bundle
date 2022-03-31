<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


use Tpg\HeadlessBundle\Query\Pageable;

final class PageableContextBuilder
{
    use ExtensionContextBuilderTrait;

    public function withPageable(Pageable $pageable): self
    {
        return $this->with(PageableExtension::CONTEXT_KEY,$pageable);
    }
}