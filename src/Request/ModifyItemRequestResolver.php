<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class ModifyItemRequestResolver implements ArgumentValueResolverInterface
{

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return is_subclass_of($argument->getType(), ModifyItemRequest::class) && $this->isModifyMethod($request);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $rawData = json_decode($request->getContent(), true, 512, JSON_THROW_ON_ERROR);
        yield [$argument->getType(),'create']($rawData);
    }

    private function isModifyMethod(Request $request):bool
    {
        return $request->isMethod(Request::METHOD_POST) || $request->isMethod(Request::METHOD_PUT);
    }
}