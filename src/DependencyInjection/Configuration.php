<?php

namespace Devtrw\StateBridgeBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

/**
 * @author  Steven Nance <steven@devtrw.com>
 * @package Devtrw\StateBridgeBundle\DependencyInjection
 */
class Configuration implements ConfigurationInterface
{
    const MAX_NESTED_STATES = 5;

    /**
     * This is the class that validates and merges configuration from your app/config files
     * To learn more
     * {@link http://symfony.com/doc/current/cookbook/bundles/extension.html#cookbook-bundles-extension-config-class}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode    = $treeBuilder->root('devtrw_state_bridge');

        // @formatter:off
        $rootNode
            ->children()
            ->arrayNode('states')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->scalarNode('route_prefix')->end()
                        ->scalarNode('entity')->defaultNull()->end()
                        ->booleanNode('abstract')->defaultFalse()->end()
                        ->booleanNode('static')->defaultFalse()->end()
                        ->append($this->stateNode())
                    ->end()
                ->end()
            ->end()
            ->scalarNode('jsonp_callback_fn')->defaultValue('loadStates')->end()
        ->end();
        // @formatter:on

        return $treeBuilder;
    }

    /**
     * @param int $currentMenuDepth
     *
     * @return ArrayNodeDefinition
     */
    private function stateNode($currentMenuDepth = 0)
    {
        $childrenTreeBuilder = new TreeBuilder();
        if (self::MAX_NESTED_STATES <= $currentMenuDepth) {
            $children = $childrenTreeBuilder->root('EOL', 'scalar');
        } else {
            $currentMenuDepth++;
            $children = $this->stateNode($currentMenuDepth);
        }

        $treeBuilder = new TreeBuilder();
        $stateNode   = $treeBuilder->root('children');

        // @formatter:off
        $stateNode
            ->prototype('array')
                ->children()
                    ->booleanNode('abstract')->defaultFalse()->end()
                    ->booleanNode('static')->defaultFalse()->end()
                    ->scalarNode('access')->defaultNull()->end()
                    ->scalarNode('icon')->end()
                    ->scalarNode('name')->end()
                    ->scalarNode('route')->end()
                    ->scalarNode('route_prefix')->end()
                    ->scalarNode('access')->end()
                    ->append($children)
                ->end()
            ->end()
        ->end();
        // @formatter:on

        return $stateNode;
    }
}
