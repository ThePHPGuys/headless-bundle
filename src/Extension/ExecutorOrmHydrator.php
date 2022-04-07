<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Extension;


interface ExecutorOrmHydrator
{
    public const HYDRATE_COLLECTION_CONTEXT = 'hydrate_collection';

    public function supportsHydration(array $data, array $context):bool;
    public function hydrate(array $data, array $context):array;
}