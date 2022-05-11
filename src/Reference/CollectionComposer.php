<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


final class CollectionComposer
{
    private \Closure $processor;
    private PromiseCollection $promises;
    /**
     * @psalm-var array<string, \Closure>
     */
    private $providers = [];
    /**
     * @var callable
     */
    private \Closure $providerGenerator;

    public function __construct(PromiseCollection $promises,callable $processor, callable $providerGenerator)
    {
        $this->processor = $processor;
        $this->promises = $promises;
        $this->providerGenerator = $providerGenerator;
    }

    public function compose(array $data):array
    {
        $processor = $this->processor;
        $result = $processor($data);
        $this->processResources();
        return $result;
    }

    private function processResources()
    {
        $promises = $this->promises->release();

        if (0 === count($promises)) {
            return;
        }

        $groupedPromises = $this->groupByResourceTypes($promises);
        $this->providers = $this->generateProviders($promises);
        foreach ($groupedPromises as $resourceType => $group) {
            $this->handleGroup($resourceType, $group);
        }

        $this->processResources();
    }

    private function handleGroup(string $resourceType, array $group)
    {
        $processor = $this->processor;
        if (!isset($this->providers[$resourceType])) {
            throw new \RuntimeException('Unknown resource type '.$resourceType);
        }
        /** @var Promise $promises */
        $promises = array_column($group, 0);
        $resources = array_column($group, 1);
        $data = ($this->providers[$resourceType])(...$resources);
        if (count($data) !== count($resources)) {
            throw new \RuntimeException(sprintf("IncorrectNumberOfResolvedResources %s, %d vs %d",$resourceType, count($resources), count($data)));
        }

        foreach (array_values($data) as $i => $item) {
            /** @var Promise $promise */
            $promise = $promises[$i];
            $promise->resolve($processor($item));
        }
    }


    private function groupByResourceTypes(array $promises):array
    {
        $groups = [];
        /**
         * @var Promise $promise
         * @var ResourceReference $resource
         */
        foreach ($promises as [$promise, $resource]) {
            $groups[$resource->type()][] = [$promise, $resource];
        }

        return $groups;
    }

    private function generateProviders(array $promises):array
    {
        $providers = [];
        $generator = $this->providerGenerator;
        /**
         * @var Promise $promise
         * @var ResourceReference $resource
         */
        foreach ($promises as [$promise, $resource]) {
            if(array_key_exists($resource->type(),$providers)){
                continue;
            }
            $providers[$resource->type()] = $generator($resource);

        }

        return $providers;
    }
}