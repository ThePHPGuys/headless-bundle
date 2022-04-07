<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


final class ExecutorOrmHydratorContextBuilder
{
    use ExtensionContextBuilderTrait;

    public function withCollection(string $collection):self
    {
        return $this->with(ExecutorOrmHydrator::HYDRATE_COLLECTION_CONTEXT,$collection);
    }
}