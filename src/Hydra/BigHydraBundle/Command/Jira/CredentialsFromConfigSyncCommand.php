<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\IssueSync;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class CredentialsFromConfigSyncCommand extends ContainerAwareCommand
{
    const NAME = 'hydra:jira:credentials-from-config-sync';

    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setDescription('Sync the Jira tickets using the configured credentials')
        ;
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        /** @var IssueSync $syncService */
        $syncService = $this->getContainer()->get('hydra_big_hydra.jira.issuesync');
        $syncService->sync();
    }
}
