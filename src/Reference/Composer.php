<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


final class Composer
{
    private \Closure $processor;
    private PromiseCollection $promises;
    /**
     * @psalm-var array<string, \Closure>
     */
    private $providers = [];

    public function __construct(PromiseCollection $promises, callable $processor = null)
    {
        $this->processor = $processor??static fn(array $data)=>$data;
        $this->promises = $promises;
    }

    public function registerProvider(string $resourceType, callable $provider):void
    {
        $this->providers[$resourceType] = \Closure::fromCallable($provider);
    }

    public function process(array $data):array
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
}