<?php

/*
 * This file is part of the StateMachine package.
 *
 * (c) Alexandre Bacco
 *
 * For the full copyright and license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace winzou\Bundle\StateMachineBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\NodeBuilder;
use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritDoc}
     */
    public function getConfigTreeBuilder()
    {
        if (\method_exists(TreeBuilder::class, 'getRootNode')) {
            $treeBuilder = new TreeBuilder('winzou_state_machine');
            $configNode = $treeBuilder->getRootNode();
        } else {
            $treeBuilder = new TreeBuilder();
            $configNode = $treeBuilder->root('winzou_state_machine');
        }

        $configNode = $configNode
            ->useAttributeAsKey('name')
            ->prototype('array')
            ->children()
        ;

        $configNode
            ->scalarNode('class')->isRequired()->end()
            ->scalarNode('graph')->defaultValue('default')->end()
            ->scalarNode('property_path')->defaultValue('state')->end()
            ->scalarNode('state_machine_class')->defaultValue('SM\\StateMachine\\StateMachine')->end()
        ;

        $this->addStateSection($configNode);
        $this->addTransitionSection($configNode);
        $this->addCallbackSection($configNode);

        $configNode->end()->end();

        return $treeBuilder;
    }

    /**
     * @param NodeBuilder $configNode
     */
    protected function addStateSection(NodeBuilder $configNode)
    {
        $configNode
            ->arrayNode('states')
                ->useAttributeAsKey('name')
                ->prototype('scalar')
            ->end()
        ;
    }

    /**
     * @param NodeBuilder $configNode
     */
    protected function addTransitionSection(NodeBuilder $configNode)
    {
        $configNode
            ->arrayNode('transitions')
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->arrayNode('from')
                            ->prototype('scalar')->end()
                        ->end()
                        ->scalarNode('to')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }

    /**
     * @param NodeBuilder $configNode
     */
    protected function addCallbackSection(NodeBuilder $configNode)
    {
        $callbacks = $configNode->arrayNode('callbacks')->children();

        $this->addSubCallbackSection($callbacks, 'guard');
        $this->addSubCallbackSection($callbacks, 'before');
        $this->addSubCallbackSection($callbacks, 'after');

        $callbacks->end()->end();
    }

    /**
     * @param NodeBuilder $callbacks
     * @param string      $type
     */
    protected function addSubCallbackSection(NodeBuilder $callbacks, $type)
    {
        $callbacks
            ->arrayNode($type)
                ->useAttributeAsKey('name')
                ->prototype('array')
                    ->children()
                        ->variableNode('on')->end()
                        ->variableNode('from')->end()
                        ->variableNode('to')->end()
                        ->variableNode('excluded_on')->end()
                        ->variableNode('excluded_from')->end()
                        ->variableNode('excluded_to')->end()
                        ->variableNode('do')->end()
                        ->scalarNode('disabled')->defaultValue(false)->end()
                        ->scalarNode('priority')->defaultValue(0)->end()
                        ->arrayNode('args')->performNoDeepMerging()->prototype('scalar')->end()
                    ->end()
                ->end()
            ->end()
        ;
    }
}
