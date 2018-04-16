<?php

namespace Top10\CabinetBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * This is the class that validates and merges configuration from your app/config files
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
 */
class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('top10_cabinet');

        $rootNode
            ->children()
                ->arrayNode('emails')
                    ->requiresAtLeastOneElement()
                    ->defaultValue( array() )
                    /*->prototype('array')
                        ->children()
                            ->scalarNode('email')->isRequired()->end()
                        ->end()
                    ->end()*/
                    ->prototype('scalar')->isRequired()->end()
                ->end()
            ->end();

        return $treeBuilder;
    }
}
