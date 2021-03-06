<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\DependencyInjection;

use Symfony\Component\Config\FileLocator;
use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\DependencyInjection\Extension\Extension;
use Symfony\Component\DependencyInjection\Loader\PhpFileLoader;
use Tpg\HeadlessBundle\Extension\ExecutorOrmExtension;
use Tpg\HeadlessBundle\Extension\ExecutorOrmHydrator;
use Tpg\HeadlessBundle\Middleware\Middleware;
use Tpg\HeadlessBundle\Service\SchemaService;

final class TpgHeadlessExtension extends Extension
{
    public const QUERY_MIDDLEWARE_TAG = 'headless.query.middleware';

    public function load(array $configs, ContainerBuilder $container)
    {
        $container->registerForAutoconfiguration(Middleware::class)
            ->addTag(self::QUERY_MIDDLEWARE_TAG);

        $loader = new PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');

        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);
        $this->addCollections($config['collections'], $container);
    }

    private function addCollections(array $config,ContainerBuilder $container){
        $definition = $container->getDefinition(SchemaService::class);
        foreach ($config as $collectionName=>$collectionConfig)
        {
            $definition->addMethodCall('addCollection',[$collectionName,$collectionConfig['class']]);
        }
    }

}