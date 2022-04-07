<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle;


use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\HttpKernel\Bundle\Bundle;
use Tpg\HeadlessBundle\DependencyInjection\TpgHeadlessCompilerPass;

final class TpgHeadlessBundle extends Bundle
{
    public function build(ContainerBuilder $container)
    {
        $container->addCompilerPass(new TpgHeadlessCompilerPass());
    }
}