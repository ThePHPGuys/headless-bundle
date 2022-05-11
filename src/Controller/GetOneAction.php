<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Controller;


use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Service\AstFactory;
use Tpg\HeadlessBundle\Service\ReadExecutor;
use Tpg\HeadlessBundle\Service\ItemsService;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final class GetOneAction
{
    private ItemsService $itemsService;
    private NormalizerInterface $normalizer;

    public function __construct(ItemsService $itemsService, NormalizerInterface $normalizer)
    {
        $this->itemsService = $itemsService;
        $this->normalizer = $normalizer;
    }

    public function __invoke(string $collection, string $id, Fields $fields):Response
    {
        $data = $this->itemsService->getOne(
            $collection,
            $id,
            $fields
        );
        return new JsonResponse($this->normalizer->normalize($data,'json'));
    }


}