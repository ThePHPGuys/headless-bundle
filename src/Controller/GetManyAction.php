<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Tpg\HeadlessBundle\Filter\Filters;
use Tpg\HeadlessBundle\Middleware\FiltersContextBuilder;
use Tpg\HeadlessBundle\Query\Fields;
use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Service\ItemsService;

final class GetManyAction
{
    private ItemsService $itemsService;
    private NormalizerInterface $normalizer;

    public function __construct(ItemsService $itemsService, NormalizerInterface $normalizer)
    {
        $this->itemsService = $itemsService;
        $this->normalizer = $normalizer;
    }

    public function __invoke(string $collection, Fields $fields, Pageable $pageable, ?Filters $filters):Response
    {
        $context = [];
        if($filters!==null) {
            $context = FiltersContextBuilder::create($context)->withFilters($filters)->toArray();
        }
        $page = $this->itemsService->getPage($collection,$fields,$pageable,$context);
        return new JsonResponse($this->normalizer->normalize($page,'json'));
    }


}