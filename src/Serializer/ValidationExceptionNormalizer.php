<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;

use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tpg\HeadlessBundle\Exception\ValidationException;

final class ValidationExceptionNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param ValidationException $object
     * @return array
     */
    public function normalize($object, string $format = null, array $context = [])
    {
        return ['error'=>$this->normalizer->normalize($object->violationList)];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return $data instanceof ValidationException;
    }


}