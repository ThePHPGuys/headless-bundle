<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Reference;


final class Link
{
    private \Closure $transformer;
    private iterable $references;
    private iterable $referencedData;

    public function __construct(iterable $references, iterable $referencedData)
    {
        $this->references = $references;
        $this->referencedData = $referencedData;
        $this->transformer = static fn($data)=>$data;
    }

    public function oneToOne(\Closure $referenceKey, \Closure $referenceDataKey): array
    {
        $map = [];
        foreach ($this->referencedData as $referencedData) {
            $map[$referenceDataKey($referencedData)] = $referencedData;
        }

        return $this->map($referenceKey, $map);
    }

    public function oneToMany(\Closure $referenceKey, \Closure $referenceDataKey): array
    {
        $map = [];
        foreach ($this->referencedData as $referencedData) {
            $map[$referenceDataKey($referencedData)][] = $referencedData;
        }
        foreach ($this->references as $reference) {
            $key = $referenceKey($reference);
            if (!array_key_exists($key, $map)) {
                $map[$key] = [];
            }
        }

        return $this->map($referenceKey, $map);
    }

    public function withTransformation(\Closure $transformer)
    {
        $copy = new self($this->references, $this->referencedData);
        $copy->transformer = $transformer;

        return $copy;
    }

    private function map(\Closure $referenceKey, array $referencedDataMap)
    {
        $transformer = $this->transformer;
        $result = [];
        foreach ($this->references as $reference) {
            $key = $referenceKey($reference);
            $result[] = array_key_exists($key, $referencedDataMap)
                ? $transformer($referencedDataMap[$key], $reference)
                : null;
        }

        return $result;
    }
}