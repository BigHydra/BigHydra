<?php

use Symfony\Component\DependencyInjection\Definition;
use Symfony\Component\DependencyInjection\Reference;
use Symfony\Component\DependencyInjection\Parameter;

$container->setDefinition(
    'hydra_jira_rest.service_factory',
    new Definition(
        'Hydra\JiraRestApiBundle\Service\ServiceFactory'
    )
);
