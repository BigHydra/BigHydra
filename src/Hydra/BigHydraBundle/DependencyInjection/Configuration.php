<?php

namespace Hydra\BigHydraBundle\DependencyInjection;

use Symfony\Component\Config\Definition\Builder\ArrayNodeDefinition;
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

        $this->buildMongoNode($jiraNode);
        $this->buildMailNode($jiraNode);

        return $treeBuilder;
    }

    /**
     * @param ArrayNodeDefinition $jiraNode
     */
    protected function buildMongoNode(ArrayNodeDefinition $jiraNode)
    {
        $mongoNode = $jiraNode->children()->arrayNode('mongo')->addDefaultsIfNotSet();
        $mongoNode->children()
            ->scalarNode('server')->defaultValue('mongodb://localhost:27017')->end()
            ->scalarNode('db')->defaultValue('jira')->end()
            ->scalarNode('collection')->defaultValue('issue')->end()
            ->end();
    }

    /**
     * @param ArrayNodeDefinition $jiraNode
     */
    protected function buildMailNode(ArrayNodeDefinition $jiraNode)
    {
        $reportNode = $jiraNode->children()->arrayNode('report');
        $mailNode = $reportNode->children()->arrayNode('mail');
        $mailNode->children()
            ->scalarNode('sender')->end()
            ->scalarNode('cc')->end()
            ->scalarNode('debug')->end()
            ->scalarNode('debug_target')->end()
            ->end();
    }
}
