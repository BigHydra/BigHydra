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
    'hydra_big_hydra.jira.publish.csv.byauthorandday',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Publish\Csv\ByAuthorAndDayMailCsv'
    )
);

$container->setDefinition(
    'hydra_big_hydra.jira.publish.mail.hydraweeklyreportmail',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Publish\Mail\HydraWeeklyReportMail',
        array(
            new Reference('mailer'),
        )
    )
);
$container->setDefinition(
    'hydra_big_hydra.jira.report.mailconfig',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Publish\Mail\MailConfig',
        array(
            new Parameter('jira.report.mail'),
        )
    )
);
$container->setDefinition(
    'hydra_big_hydra.jira.report.hydraweeklyreport',
    new Definition(
        'Hydra\BigHydraBundle\Jira\HydraWeeklyReport',
        array(
            new Reference('hydra_big_hydra.jira.report.mailconfig'),
            new Reference('hydra_big_hydra.jira.mongo_repository'),
            new Reference('hydra_big_hydra.jira.publish.mail.hydraweeklyreportmail'),
        )
    )
);

$container->setDefinition(
    'hydra_big_hydra.jira.report',
    new Definition(
        'Hydra\BigHydraBundle\Jira\IssueReport',
        array(
            new Reference('hydra_big_hydra.jira.mongo_repository'),
        )
    )
);
$container->setDefinition(
    'hydra_big_hydra.jira.mentionedreport',
    new Definition(
        'Hydra\BigHydraBundle\Jira\MentionedReport',
        array(
            new Reference('hydra_big_hydra.jira.mongo_repository'),
        )
    )
);

$container->setDefinition(
    'hydra_big_hydra.jira.issueservice',
    new Definition(
        'Hydra\JiraRestApiBundle\Service\IssueService'
    )
)->setFactoryService('hydra_jira_rest.service_factory')
    ->setFactoryMethod('getIssueService')
    ->addArgument(new Parameter('jira.auth.host'))
    ->addArgument(new Parameter('jira.auth.username'))
    ->addArgument(new Parameter('jira.auth.password'));
$container->setDefinition(
    'hydra_big_hydra.jira.extractjiraissue',
    new Definition(
        'Hydra\BigHydraBundle\Jira\Extract\ExtractJiraIssue',
        array(
            new Reference('hydra_big_hydra.jira.issueservice'),
        )
    )
);
$container->setDefinition(
    'hydra_big_hydra.jira.issuesync',
    new Definition(
        'Hydra\BigHydraBundle\Jira\IssueSync',
        array(
            new Reference('hydra_big_hydra.jira.mongo_repository'),
            new Reference('hydra_big_hydra.jira.extractjiraissue'),
            new Parameter('jira.sync'),
        )
    )
);

$container->setDefinition(
    'hydra_big_hydra.jira.commentgrep',
    new Definition(
        'Hydra\BigHydraBundle\Jira\CommentGrep',
        array(
            new Reference('hydra_big_hydra.jira.mongo_repository'),
        )
    )
);
