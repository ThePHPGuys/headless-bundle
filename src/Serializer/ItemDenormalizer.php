<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;

use Tpg\HeadlessBundle\Schema\Relation;
use Tpg\HeadlessBundle\Service\SchemaService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

final class ItemDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    public const COLLECTION = '_item_collection';
    public const OPERATION = '_item_operation';
    private SchemaService $schemaService;
    private EntityManagerInterface $entityManager;

    public function __construct(SchemaService $schemaService,EntityManagerInterface $entityManager)
    {
        $this->schemaService = $schemaService;
        $this->entityManager = $entityManager;
    }


    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return isset($context['deserialization_path'],$context['collection'])
            && $this->schemaService->hasRelation($context['collection'], $context['deserialization_path']);
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        $fieldName = $context['deserialization_path'];
        $collection = $context['collection'];
        $relation = $this->schemaService->getRelation($collection,$fieldName);
        if($relation->isComposition()){
            unset($context['collection']);
            return $this->denormalizer->denormalize($data,$type,$format,$context);
        }
        $relatedCollectionClass = $this->schemaService->getCollection($relation->collection)->class;

        return $this->entityManager->getReference($relatedCollectionClass,$this->getId($relation, $data));
    }

    private function getId(Relation $relation, $value)
    {
        $referencedField = $this->schemaService->getField($relation->collection,$relation->referencedColumn);
        return $this->entityManager->getConnection()->convertToPHPValue($value,$referencedField->type);
    }



}