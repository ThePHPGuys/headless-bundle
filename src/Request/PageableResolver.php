<?php

namespace Tpg\HeadlessBundle\Request;

use Tpg\HeadlessBundle\Query\Pageable;
use Tpg\HeadlessBundle\Query\Sort;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Controller\ArgumentValueResolverInterface;
use Symfony\Component\HttpKernel\ControllerMetadata\ArgumentMetadata;


class PageableResolver implements ArgumentValueResolverInterface
{
    private const PAGE_ATTR = '_page';
    private const SIZE_ATTR = '_size';
    private const DEFAULT_SIZE = 50;
    private const SORT_ATTR = '_sort';

    public function supports(Request $request, ArgumentMetadata $argument): bool
    {
        return $argument->getType() === Pageable::class;
    }


    public function resolve(Request $request, ArgumentMetadata $argument): \Generator
    {
        yield $this->resolvePagination($request)->withSort($this->resolveSort($request));
    }

    private function resolvePagination(Request $request):PageRequest
    {
        $page = $request->query->getInt(self::PAGE_ATTR,0);
        if($page){
            //Convert to Zero-based numbering
            $page--;
        }
        $size = $request->query->getInt(self::SIZE_ATTR,self::DEFAULT_SIZE);
        return PageRequest::of($page,$size);
    }

    private function resolveSort(Request $request):Sort
    {
        $sortRequest = $request->query->get(self::SORT_ATTR);

        if(!is_array($sortRequest) || count($sortRequest)===0){
            return Sort::unsorted();
        }

        $orders = [];

        foreach ($sortRequest as $property => $direction)
        {
            $orders[] = Sort\Order::by($property,Sort\Direction::fromString($direction));
        }

        return Sort::by(...$orders);


    }
}
