<?php

namespace Hydra\BigHydraBundle\Command\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\JiraReports;
use Hydra\BigHydraBundle\Library\DateCalculator;
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
        $dates = $this->getWeekDays($week);

        /** @var JiraReports $timelog */
        $timelog = $this->getContainer()->get('hydra_big_hydra.service.jirareports');
        $log = $timelog->getByAuthorAndDay($authors, $dates);

        $filename = sprintf(
            '%s_KW%s_%s.csv',
            date("YmdHis"),
            $week,
            implode("_", $authors)
        );
        $fp = fopen($filename, 'w');
        fputcsv($fp, array_keys(current($log)));
        foreach ($log as $logLine) {
            fputcsv($fp, $logLine);
        }
        fclose($fp);

        echo "$filename\n";
//        echo "Successfully written into 'timelog.csv'\n";
//        echo "Showing first entry: \n";
//        echo json_encode(
//            array_slice($log, 0, 1)[0],
//            JSON_PRETTY_PRINT
//        );
    }

    /**
     * @param string $week
     *
     * @return \string[]
     * @throws \RuntimeException
     */
    protected function getWeekDays(&$week)
    {
        $dateCalc = new DateCalculator();
        switch ($week) {
            case 'LW':
                $week = $dateCalc->getLastWeekNumber();
                break;
            case 'CW':
                $week = $dateCalc->getCurrentWeekNumber();
                break;
        }
        if (!is_numeric($week)) {
            throw new \RuntimeException(
                sprintf(
                    'Wrong week "%s" provided!',
                    $week
                )
            );
        }
        $dates = $dateCalc->getDayRangesOfWeek($week);
        return $dates;
    }
}
