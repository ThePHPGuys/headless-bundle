<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\DependencyInjection;


use Symfony\Component\DependencyInjection\Compiler\CompilerPassInterface;
use Symfony\Component\DependencyInjection\Compiler\PriorityTaggedServiceTrait;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Tpg\HeadlessBundle\Service\ExecutorORM;

final class TpgHeadlessCompilerPass implements CompilerPassInterface
{
    use PriorityTaggedServiceTrait;

    public function process(ContainerBuilder $container)
    {
        $extensions = $this->findAndSortTaggedServices(TpgHeadlessExtension::EXTENSION_TAG,$container);
        $hydrators = $this->findAndSortTaggedServices(TpgHeadlessExtension::HYDRATOR_TAG,$container);

        $executorDefinition = $container->getDefinition(ExecutorORM::class);

        $executorDefinition->setArgument('$extensions',$extensions);
        $executorDefinition->setArgument('$hydrators',$hydrators);
    }

}
