<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\IssueReportByAuthorAndDay;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 *
 */
class ReportByAuthorAndDayCommand extends ContainerAwareCommand
{
    /**
     * {@inheritdoc}
     */
    protected function configure()
    {
        $this
            ->setName('hydra:jira:report:author-and-day')
            ->setDescription('Time log report grouped by author and day')
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
        /** @var IssueReportByAuthorAndDay $report */
        $report = $this->getContainer()->get('hydra_big_hydra.jira.report.byauthorandday');
        $filename = $report->getByAuthorAndDay($week, $authors);

        echo "$filename\n";
//        echo "Successfully written into 'timelog.csv'\n";
//        echo "Showing first entry: \n";
//        echo json_encode(
//            array_slice($log, 0, 1)[0],
//            JSON_PRETTY_PRINT
//        );
    }
}
