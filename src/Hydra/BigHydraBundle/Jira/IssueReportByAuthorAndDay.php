<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndDay;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Jira\Publish\Csv\ByAuthorAndDayCsv;
use Hydra\BigHydraBundle\Jira\Publish\Mail\ByAuthorAndDayMail;
use Hydra\BigHydraBundle\Library\DateCalculator;

class IssueReportByAuthorAndDay
{
    /** @var MongoRepository */
    protected $mongoRepo;
    /** @var ByAuthorAndDayCsv */
    protected $csvConverter;

    /**
     * @param MongoRepository $mongoRepo
     */
    public function __construct(MongoRepository $mongoRepo)
    {
        $this->mongoRepo = $mongoRepo;
        $this->csvConverter = new ByAuthorAndDayCsv();
    }

    /**
     * @param string $week
     * @param array $author
     *
     * @return string
     */
    public function getByAuthorAndDay($week, array $author)
    {
        $dateFilter = $this->getWeekDays($week);
        $rawReport = $this->executeReport($author, $dateFilter);
        $result = $this->createCsvReadyExport($rawReport);

        $filename = $this->buildCsvFileName($week, $author);
        $this->csvConverter->saveToFile($filename, $result);
//        $mailer = new ByAuthorAndDayMail();
//        $mailer->sendMail($result);

        return $filename;
    }

    /**
     * @param array $author
     * @param array $validDates
     * @return mixed
     */
    protected function executeReport(array $author, array $validDates)
    {
        $report = new ByAuthorAndDay($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();
        return $rawReport;
    }

    /**
     * @param $rawReport
     * @return array
     */
    protected function createCsvReadyExport($rawReport)
    {
        $result = $this->csvConverter->flatten($rawReport);
        return $result;
    }

    protected function buildCsvFileName($week, $authors)
    {
        $filename = sprintf(
            '%s_KW%s',
            date("YmdHis"),
            $week
        );
        if (0 === count($authors)) {
            $filename .= '_all';
        } else {
            $filename .= '_' . implode("_", $authors);
        }

        $filename .= '.csv';

        return $filename;
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
