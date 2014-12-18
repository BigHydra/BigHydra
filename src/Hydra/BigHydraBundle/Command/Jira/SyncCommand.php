<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\Extract\ExtractJiraIssue;
use Hydra\BigHydraBundle\Jira\IssueSync;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\JiraRestApiBundle\Service\ServiceFactory;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class SyncCommand extends ContainerAwareCommand
{
    const NAME = 'hydra:jira:sync';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setDescription('Sync the Jira tickets')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('username', InputArgument::REQUIRED)
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $host = $input->getArgument('host');
        $username = $input->getArgument('username');

        /** @var DialogHelper $interactiveDialog */
        $interactiveDialog = $this->getHelperSet()->get('dialog');
        $password = $interactiveDialog->askHiddenResponse(
            $output,
            'Enter password: ',
            null
        );

        if (null === $password) {
            throw new \InvalidArgumentException("You have to provide a password");
        }

        $syncService = $this->instantiateIssueSync($host, $username, $password);
        $syncService->sync();
    }

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     *
     * @return IssueSync
     */
    protected function instantiateIssueSync($host, $username, $password)
    {
        /** @var MongoRepository $repository */
        $repository = $this->getContainer()->get('hydra_big_hydra.jira.mongo_repository');
        /** @var ServiceFactory $jiraFactory */
        $jiraFactory = $this->getContainer()->get('hydra_jira_rest.service_factory');

        $issueService = $jiraFactory->getIssueService($host, $username, $password);
        $extractIssue = new ExtractJiraIssue($issueService);

        $syncConfig = $this->getContainer()->getParameter('jira.sync');
        $syncService = new IssueSync($repository, $extractIssue, $syncConfig);

        return $syncService;
    }
}
