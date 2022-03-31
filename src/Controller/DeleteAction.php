<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Controller;

use Tpg\HeadlessBundle\Service\ItemsService;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/{collection}/{id}/",methods="DELETE")
 */
final class DeleteAction
{
    private ItemsService $itemsService;

    public function __construct(ItemsService $itemsService)
    {
        $this->itemsService = $itemsService;
    }

    public function __invoke(string $collection, string $id)
    {
        $this->itemsService->delete($collection, $id);
        return new Response();

    }
}