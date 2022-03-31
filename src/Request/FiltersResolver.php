<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\Request;


use Tpg\HeadlessBundle\Extension\Filter\Filters;
use InvalidArgumentException;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;

final class FiltersResolver implements ArgumentValueResolverInterface
{
    private string $filterAttributeName;
    public function __construct(string $filterAttributeName = '_filters')
    {
        $this->filterAttributeName = $filterAttributeName;
    }

    public function supports(Request $request, ArgumentMetadata $argument)
    {
        return Filters::class === $argument->getType() && $request->query->get($this->filterAttributeName);
    }

    public function resolve(Request $request, ArgumentMetadata $argument)
    {
        $filterString = $request->query->get($this->filterAttributeName);

        $filtersArray = json_decode($filterString, true);

        if (!is_array($filtersArray)) {
            return null;
        }

        try {
            yield Filters::createFromArray($filtersArray);
        } catch (InvalidArgumentException $e) {
            yield null;
        }


    }


}