<?php

namespace Hydra\BigHydraBundle\DependencyInjection;

use Symfony\Component\DependencyInjection\ContainerBuilder;
use Symfony\Component\Config\FileLocator;
use Symfony\Component\HttpKernel\DependencyInjection\Extension;
use Symfony\Component\DependencyInjection\Loader;

/**
 * This is the class that loads and manages your bundle configuration
 *
 * To learn more see {@link http://symfony.com/doc/current/cookbook/bundles/extension.html}
 */
class HydraBigHydraExtension extends Extension
{
    /**
     * {@inheritdoc}
     */
    public function load(array $configs, ContainerBuilder $container)
    {
        $configuration = new Configuration();
        $config = $this->processConfiguration($configuration, $configs);

        $jiraConfig = $config['jira'];

        $container->setParameter('jira.auth.host', $jiraConfig['auth']['host']);
        $container->setParameter('jira.auth.username', $jiraConfig['auth']['username']);
        $container->setParameter('jira.auth.password', $jiraConfig['auth']['password']);

        $container->setParameter('jira.sync', $jiraConfig['sync']);

        $container->setParameter('jira.mongo.server', $jiraConfig['mongo']['server']);
        $container->setParameter('jira.mongo.db', $jiraConfig['mongo']['db']);
        $container->setParameter('jira.mongo.collection', $jiraConfig['mongo']['collection']);

        $container->setParameter('jira.report.mail', $jiraConfig['report']['mail']);

        $loader = new Loader\PhpFileLoader($container, new FileLocator(__DIR__.'/../Resources/config'));
        $loader->load('services.php');
    }
}
