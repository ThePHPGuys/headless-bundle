<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;
use Tpg\HeadlessBundle\Schema\Schema;

final class ItemDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    private EntityManagerInterface $entityManager;
    private array $collectionClassNames = [];
    private array $itemsCache = [];

    public function __construct(EntityManagerInterface $entityManager, Schema $schema)
    {
        $this->entityManager = $entityManager;
        foreach ($schema->getCollections() as $collection) {
            $collectionClass = $schema->getCollection($collection)->class;
            $collectionIdField = $schema->getIdentifier($collection)->fieldName;
            $this->collectionClassNames[$collectionClass] = $collectionIdField;
        }
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        if(array_key_exists($type,$this->itemsCache) && in_array($data,$this->itemsCache[$type])){
            return false;
        }
        return $this->isCollectionClass($type) && $this->isValidIdentifier($type,$data);
    }

    private function childDenormalize($object, $data, string $type, string $format = null, array $context = [])
    {
        $this->itemsCache[$type][] = $data;
        $context = [];
        $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $type;
        unset($data[$this->collectionClassNames[$type]]);
        return $this->denormalizer->denormalize($data,$type,$format,$context);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $object = $this->entityManager->find($type, $this->getIdentifier($type,$data));
        if(is_array($data)){
            //TOD: If data is array then values should be merged into entity when updating. Form O2M.M2O
            return $this->childDenormalize($object,$data,$type,$format,$context);
        }
        return $object;
    }

    private function isCollectionClass(string $class)
    {
        return array_key_exists($class, $this->collectionClassNames);
    }

    private function isValidIdentifier(string $class, $value):bool
    {
        if(is_string($value) || is_numeric($value)){
            return true;
        }
        if(!is_array($value)){
            return false;
        }
        $classIdField = $this->collectionClassNames[$class];
        return array_key_exists($classIdField, $value);
    }

    private function getIdentifier(string $class, $value)
    {
        if(is_string($value) || is_numeric($value)){
            return $value;
        }

        if(!is_array($value)){
            throw new \RuntimeException('Invalid identifier value');
        }

        $classIdField = $this->collectionClassNames[$class];

        if(!array_key_exists($classIdField, $value)){
            throw new \RuntimeException('Identifier does not exists in array');
        }
        return $value[$classIdField];
    }
}