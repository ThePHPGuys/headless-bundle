<?php

declare(strict_types=1);

namespace Tpg\HeadlessBundle\DependencyInjection;


use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

final class Configuration implements ConfigurationInterface
{
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('tpg_headless');

        $treeBuilder->getRootNode()
            ->children()
            ->arrayNode('collections')
                ->defaultValue([])
                ->useAttributeAsKey('name')
                ->arrayPrototype()
                    ->beforeNormalization()
                    ->ifString()
                    ->then(static function ($v) {
                        return ['class' => $v];
                    })
                    ->end()
                    ->children()
                        ->scalarNode('class')->isRequired()->end()
                    ->end()
                ->end()
            ->end()
            ->end()
        ;

        return $treeBuilder;
    }

}