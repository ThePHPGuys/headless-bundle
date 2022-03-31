<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


use Tpg\HeadlessBundle\Query\Fields;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class FieldsResolver implements ArgumentValueResolverInterface
{
    public function supports(Request $request, ArgumentMetadata $argument):bool
    {
        return $argument->getType() === Fields::class && $request->isMethod(Request::METHOD_GET);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $fields = $request->query->get('_fields','*');
        yield new Fields(explode(',',$fields));
    }
}