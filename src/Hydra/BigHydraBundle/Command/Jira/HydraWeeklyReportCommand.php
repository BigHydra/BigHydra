<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\HydraWeeklyReport;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class HydraWeeklyReportCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('hydra:report:weekly')
            ->setDescription('Detailed email time log report for Team Hydra')
            ->addArgument('week', InputArgument::REQUIRED)
            ->addArgument('authors', InputArgument::IS_ARRAY);
    }

    /**
     * {@inheritdoc}
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $week = $input->getArgument('week');
        $authors = $input->getArgument('authors');

        $this->createReport($authors, $week);
    }

    /**
     * @param array $authors
     * @param string $week
     */
    protected function createReport(array $authors, $week)
    {
        /** @var HydraWeeklyReport $report */
        $report = $this->getContainer()->get('hydra_big_hydra.jira.report.hydraweeklyreport');
        $filename = $report->runWeeklyReport($week, $authors);
        echo implode("\n", $filename);
        echo "\n";
    }
}
