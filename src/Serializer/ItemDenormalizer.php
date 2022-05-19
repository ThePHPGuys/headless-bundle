<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\Normalizer\ContextAwareDenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerAwareTrait;

final class ItemDenormalizer implements ContextAwareDenormalizerInterface, DenormalizerAwareInterface
{
    use DenormalizerAwareTrait;
    private EntityManagerInterface $entityManager;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    public function supportsDenormalization($data, string $type, string $format = null, array $context = [])
    {
        return class_exists($type) && !$this->entityManager->getMetadataFactory()->isTransient($type) && (is_string($data) || is_numeric($data));
    }

    public function denormalize($data, string $type, string $format = null, array $context = [])
    {
        return $this->entityManager->find($type, $data);
    }
}