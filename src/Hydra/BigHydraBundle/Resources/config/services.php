<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

$container->setDefinition(
    'hydra_big_hydra.jira.mongo_client',
    new Definition(
        'MongoClient',
        array(
            new Parameter('jira.mongo.server'),
        )
    )
);

$container->setDefinition(
    'hydra_big_hydra.jira.mongo_collection',
    new Definition(
        'MongoCollection'
    )
)->setFactoryService('hydra_big_hydra.jira.mongo_client')
    ->setFactoryMethod('selectCollection')
        ->addArgument(new Parameter('jira.mongo.db'))
        ->addArgument(new Parameter('jira.mongo.collection'));

$container->setDefinition(
    'hydra_big_hydra.jira.mongo_repository',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Load\MongoRepository',
        array(
            new Reference('hydra_big_hydra.jira.mongo_collection')
        )
    )
);

$container->setDefinition(
    'hydra_big_hydra.service.jirareports',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Analyse\JiraReports',
        array(
            new Reference('hydra_big_hydra.jira.mongo_repository')
        )
    )
);
