<?php

namespace Hydra\JiraRestApiBundle\Command;

use Hydra\JiraRestApiBundle\Service\ServiceFactory;
use Sensio\Bundle\GeneratorBundle\Command\Helper\DialogHelper;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TestCommand extends ContainerAwareCommand
{
    const NAME = 'hydra:jira:test';

    protected function configure()
    {
        $this
            ->setName(static::NAME)
            ->setDescription('Test the Jira connection, get your user profile')
            ->addArgument('host', InputArgument::REQUIRED)
            ->addArgument('username', InputArgument::REQUIRED);
    }

    /**
     * {@inheritDoc}
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
            false
        );

        if (false === $password) {
            throw new \InvalidArgumentException("You have to provide a password");
        }

        /** @var ServiceFactory $serviceFactory */
        $serviceFactory = $this->getContainer()->get('hydra_jira_rest.service_factory');
        $user = $serviceFactory->getIssueService($host, $username, $password)->getUser($username);

        $output->writeln(json_encode($user, JSON_PRETTY_PRINT));
    }
}
