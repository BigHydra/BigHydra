<?php

namespace Hydra\BigHydraBundle\DependencyInjection;

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
     * {@inheritdoc}
     */
    public function getConfigTreeBuilder()
    {
        $treeBuilder = new TreeBuilder();
        $rootNode = $treeBuilder->root('hydra_big_hydra');

        $jiraNode = $rootNode->children()->arrayNode('jira');
        $mongoNode = $jiraNode->children()->arrayNode('mongo');
        $mongoNode->children()
            ->scalarNode('server')->defaultValue('mongodb://localhost:27017')->end()
            ->scalarNode('db')->defaultValue('jira')->end()
            ->scalarNode('collection')->defaultValue('issue')->end()
            ->end();

        return $treeBuilder;
    }
}
