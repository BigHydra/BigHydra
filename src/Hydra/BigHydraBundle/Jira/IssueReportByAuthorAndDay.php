<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndDay;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Jira\Publish\Csv\ByAuthorAndDayCsv;

class IssueReportByAuthorAndDay
{
    /** @var MongoRepository */
    protected $mongoRepo;

    /**
     * @param MongoRepository $mongoRepo
     */
    public function __construct(MongoRepository $mongoRepo)
    {
        $this->mongoRepo = $mongoRepo;
    }

    /**
     * @param array $author
     * @param array $validDates
     *
     * @return array
     */
    public function getByAuthorAndDay(array $author, array $validDates)
    {
        $rawReport = $this->executeReport($author, $validDates);
        $result = $this->createCsvReadyExport($rawReport);

        return $result;
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
        $csvConverter = new ByAuthorAndDayCsv();
        $result = $csvConverter->flatten($rawReport);
        return $result;
    }
}
