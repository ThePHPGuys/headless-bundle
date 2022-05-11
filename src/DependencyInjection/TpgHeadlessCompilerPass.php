<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tpg\HeadlessBundle\Middleware\QueryMiddlewareStack;
use Tpg\HeadlessBundle\Service\ReadExecutor;

final class TpgHeadlessCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        $middlewares = $this->findAndSortTaggedServices(TpgHeadlessExtension::QUERY_MIDDLEWARE_TAG,$container);
        $middlewaresDefinition = $container->getDefinition(QueryMiddlewareStack::class);
        $middlewaresDefinition->addMethodCall('pipes',[$middlewares]);

    }

}
