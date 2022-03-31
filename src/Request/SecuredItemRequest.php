<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


use Tpg\HeadlessBundle\Service\SecurityService;

final class SecuredItemRequest implements ModifyItemRequest
{
    public ModifyItemRequest $originalRequest;
    private array $data;

    public function __construct(ModifyItemRequest $request, SecurityService $securityService, $collection, $operation)
    {
        $this->originalRequest = $request;
        $this->data = $securityService->filterEntityData($collection,$request->getData(),$operation);
    }

    public function getData(): array
    {
        return $this->data;
    }

    public static function create(array $data): ModifyItemRequest
    {
        throw new \RuntimeException("Must be instantiated via constructor");
    }

}