<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndDay;
use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorTicketAndDay;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Jira\Publish\Csv\ByAuthorAndDayCsv;
use Hydra\BigHydraBundle\Jira\Publish\Csv\ByAuthorTicketAndDayCsv;
use Hydra\BigHydraBundle\Jira\Publish\Mail\HydraWeeklyReportMail;
use Hydra\BigHydraBundle\Jira\Publish\Mail\MailConfig;
use Hydra\BigHydraBundle\Library\DateCalculator;

class HydraWeeklyReport
{
    /** @var MailConfig */
    protected $config;
    /** @var MongoRepository */
    protected $mongoRepo;
    /** @var ByAuthorAndDayCsv */
    protected $summaryCsvConverter;
    /** @var HydraWeeklyReportMail */
    protected $mailer;

    /**
     * @param MailConfig $config
     * @param MongoRepository $mongoRepo
     * @param HydraWeeklyReportMail $mailer
     */
    public function __construct(
        MailConfig $config,
        MongoRepository $mongoRepo,
        HydraWeeklyReportMail $mailer
    ) {
        $this->config = $config;
        $this->mongoRepo = $mongoRepo;
        $this->summaryCsvConverter = new ByAuthorAndDayCsv();
        $this->detailCsvConverter = new ByAuthorTicketAndDayCsv();
        $this->mailer = $mailer;
    }

    /**
     * @param string $week
     * @param array $authors
     *
     * @return array
     */
    public function runWeeklyReport($week, array $authors)
    {
        $dateFilter = $this->getWeekDays($week);

        $fileNames = [];
        if (0 === count($authors)) {
            $fileNames = $this->prepareLeadReport($week, $authors, $dateFilter);
        } else {
            foreach ($authors as $author) {
                $fileNames[] = $this->prepareAndSendReport($week, $author, $dateFilter);
            }
        }

        return $fileNames;
    }

    /**
     * @param string $week
     * @param array $authors
     * @param array $dateFilter
     *
     * @return string
     */
    protected function prepareLeadReport($week, array $authors, array $dateFilter)
    {
        $fileNames = [];

        $summaryReport = $this->executeSummaryReport($authors, $dateFilter);
        $summaryCsvReport = $this->summaryCsvConverter->flatten($summaryReport);
        $filename = $this->buildCsvFileName($week, $authors);
        $this->detailCsvConverter->saveToFile($filename, $summaryCsvReport);
        $fileNames[] = $filename;

        $detailReport = $this->executeDetailReport($authors, $dateFilter);
        $detailCsvReport = $this->detailCsvConverter->flatten($detailReport);
        $filename = str_replace('.csv', '_detailed.csv', $filename);
        $this->detailCsvConverter->saveToFile($filename, $detailCsvReport);
        $fileNames[] = $filename;

        return $fileNames;
    }

    /**
     * @param string $week
     * @param string $author
     * @param array $dateFilter
     *
     * @return string
     */
    protected function prepareAndSendReport($week, $author, array $dateFilter)
    {
        $authors = [$author];
        $summaryReport = $this->executeSummaryReport($authors, $dateFilter);
        $summaryCsvReport = $this->summaryCsvConverter->flatten($summaryReport);

        $detailReport = $this->executeDetailReport($authors, $dateFilter);
        $detailCsvReport = $this->detailCsvConverter->flatten($detailReport);
        $filename = $this->buildCsvFileName($week, $authors);
        $this->detailCsvConverter->saveToFile($filename, $detailCsvReport);


        $this->mailer->sendMail($this->config, $week, $summaryCsvReport, $filename);

        return $filename;
    }

    /**
     * @param array $author
     * @param array $validDates
     *
     * @return array
     */
    protected function executeDetailReport(array $author, array $validDates)
    {
        $report = new ByAuthorTicketAndDay($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();

        return $rawReport;
    }

    /**
     * @param array $author
     * @param array $validDates
     *
     * @return array
     */
    protected function executeSummaryReport(array $author, array $validDates)
    {
        $report = new ByAuthorAndDay($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();

        return $rawReport;
    }

    /**
     * @param string $week
     * @param array $authors
     *
     * @return string
     */
    protected function buildCsvFileName($week, array $authors)
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
