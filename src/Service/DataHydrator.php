<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Service;


use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;

final class DataHydrator
{
    private SchemaService $schemaService;
    private DenormalizerInterface $denormalizer;

    public function __construct(SchemaService $schemaService, DenormalizerInterface $denormalizer)
    {
        $this->schemaService = $schemaService;
        $this->denormalizer = $denormalizer;
    }

    public function createObject(string $collectionName, array $data):object
    {
        $collectionMeta = $this->schemaService->getCollection($collectionName);
        return $this->denormalizer->denormalize($data,$collectionMeta->class,null,['collection'=>$collectionName]);
    }

    public function hydrateObject(string $collectionName, object $object, array $data):object
    {
        return $this->denormalizer->denormalize($data,get_class($object),null,[
            AbstractNormalizer::OBJECT_TO_POPULATE => $object,
            'collection'=>$collectionName
        ]);
    }

}