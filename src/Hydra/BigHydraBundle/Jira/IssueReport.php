<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndTicket;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Library\TimeCalculator;

class IssueReport
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
    public function getByAuthorAndTicket(array $author, array $validDates)
    {
        $report = new ByAuthorAndTicket($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();
        $result = [];
        foreach ($rawReport as $key => $value) {
            $result[$key] = $value;
            $result[$key]['affectedDates'] = implode(', ', $value['affectedDates']);
            $result[$key]['rawTime'] = $value['time'];
            $result[$key]['time'] = TimeCalculator::secondsToHumanReadableTime($value['time']);
        }

        return $result;
    }
}
