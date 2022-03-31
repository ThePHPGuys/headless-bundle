<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Serializer;


use Tpg\HeadlessBundle\Query\Page;
use Tpg\HeadlessBundle\Query\Pager;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerAwareTrait;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class PageNormalizer implements NormalizerInterface, NormalizerAwareInterface
{
    use NormalizerAwareTrait;

    /**
     * @param Page $object
     */
    public function normalize($object, string $format = null, array $context = [])
    {

        return [
            'total' => $object->getTotalElements(),
            'page' => $object->getNumber()+1,
            'data' => $this->normalizer->normalize($object->getIterator(),$format,$context)
        ];
    }

    public function supportsNormalization($data, string $format = null)
    {
        return is_object($data) && ($data instanceof Page);
    }

}