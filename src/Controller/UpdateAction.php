<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Controller;


use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Tpg\HeadlessBundle\Exception\ValidationException;
use Tpg\HeadlessBundle\Request\CreateItemRequest;
use Tpg\HeadlessBundle\Service\ItemsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{collection}/{id}/",methods="PUT")
 */
final class UpdateAction
{
    private ItemsService $itemsService;
    private NormalizerInterface $normalizer;

    public function __construct(ItemsService $itemsService, NormalizerInterface $normalizer)
    {
        $this->itemsService = $itemsService;
        $this->normalizer = $normalizer;
    }

    public function __invoke(string $collection, string $id, CreateItemRequest $request)
    {
        try {
            $this->itemsService->update($collection, $id, $request->getData());
        }catch (ValidationException $exception){
            return new JsonResponse($this->normalizer->normalize($exception),$exception->getCode());
        }
        return new Response("",Response::HTTP_ACCEPTED);
        
    }
}