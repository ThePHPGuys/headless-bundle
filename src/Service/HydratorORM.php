<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;

use Tpg\HeadlessBundle\Reference\CollectionComposer;
use Tpg\HeadlessBundle\Reference\CollectionResourceReference;
use Tpg\HeadlessBundle\Reference\PromiseCollection;

final class HydratorORM
{
    private ResourceHydratorFactory $hydratorFactory;

    public function __construct(ResourceHydratorFactory $hydratorFactory)
    {
        $this->hydratorFactory = $hydratorFactory;
    }

    public function hydrate(array $data):array
    {
        return $this->createComposer()->compose($data);
    }

    private function createComposer():CollectionComposer
    {
        $promises = new PromiseCollection();
        return new CollectionComposer($promises,$this->createProcessor($promises),$this->createProviderGenerator());
    }

    private function createProcessor(PromiseCollection $promises):callable
    {
        return fn($data)=>$this->processData($data,$promises);
    }

    private function createProviderGenerator():callable
    {
        return fn(CollectionResourceReference $ref)=>$this->hydratorFactory->generateProvider($ref);
    }

    private function processData($data, PromiseCollection $promiseCollection)
    {
        if(!is_array($data)) {
            return $data;
        }
        array_walk_recursive(
            $data,
            static fn(&$item)=>$item = ($item instanceof CollectionResourceReference)?$promiseCollection->remember($item):$item
        );

        return $data;
    }
}