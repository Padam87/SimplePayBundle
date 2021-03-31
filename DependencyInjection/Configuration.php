<?php

namespace Padam87\SimplePayBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\TreeBuilder;
use Symfony\Component\Config\Definition\ConfigurationInterface;

class Configuration implements ConfigurationInterface
{
    /**
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder('padam87_simple_pay');

        $treeBuilder->getRootNode()
            ->children()
                ->scalarNode('transaction_entity')
                    ->isRequired()
                ->end()
                ->scalarNode('backref_route')
                    ->isRequired()
                ->end()
                ->arrayNode('backref_route_parameters')
                    ->prototype('scalar')->end()
                ->end()
                ->scalarNode('base_url')
                    ->defaultNull()
                ->end()
                ->scalarNode('sandbox_url')
                    ->defaultValue('https://sandbox.simplepay.hu/payment/v2')
                ->end()
                ->scalarNode('live_url')
                    ->defaultValue('https://secure.simplepay.hu/payment/v2')
                ->end()
                ->booleanNode('sandbox')
                    ->defaultTrue()
                ->end()
                ->arrayNode('merchants')
                    ->useAttributeAsKey('currency')
                    ->requiresAtLeastOneElement()
                    ->isRequired()
                    ->arrayPrototype()
                        ->children()
                            ->scalarNode('id')->end()
                            ->scalarNode('secret')->end()
                            ->scalarNode('currency')->end()
                        ->end()
                    ->end()
                ->end()
            ->end()
        ;

        return $treeBuilder;
    }
}
