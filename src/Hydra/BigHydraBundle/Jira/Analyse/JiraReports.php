<?php
namespace Hydra\BigHydraBundle\Jira\Analyse;

use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndDay;
use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorAndTicket;
use Hydra\BigHydraBundle\Jira\Analyse\WorkLog\ByAuthorTicketAndDay;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Library\TimeCalculator;

class JiraReports
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
        $report = new ByAuthorAndDay($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();
//        print_r($rawReport);exit;
        $result = [];
        foreach ($rawReport as $value) {
            $value['date'] = sprintf(
                '%02d-%02d-%02d',
                $value['year'],
                $value['month'],
                $value['day']
            );
            unset($value['year'], $value['month'], $value['day']);
            $value['rawTime'] = $value['time'];
            $value['time'] = TimeCalculator::secondsToHumanReadableTime($value['time']);
            $value['affectedIssues'] = implode(
                ";\n",
                array_map(
                    function ($entry) {
                        return implode(' ', $entry);
                    },
                    $value['affectedIssues']
                )
            );

            $result[] = $value;
        }

        return $result;
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

    /**
     * @param array $author
     * @param array $validDates
     *
     * @return array
     */
    public function getByAuthorTicketAndDay(array $author, array $validDates)
    {
        $report = new ByAuthorTicketAndDay($this->mongoRepo);
        $report->setAuthorFilter($author);
        $report->setDateFilter($validDates);
        $rawReport = $report->runReport();
//        print_r($rawReport);exit;
        $result = [];
        foreach ($rawReport as $value) {
            $value['date'] = sprintf(
                '%02d-%02d-%02d',
                $value['year'],
                $value['month'],
                $value['day']
            );
            unset($value['year'], $value['month'], $value['day']);
            $value['rawTime'] = $value['time'];
            $value['time'] = TimeCalculator::secondsToHumanReadableTime($value['time']);

            $details = $value['details'];
            unset($value['details']);
            $detailNumber = 0;
            foreach ($details as $detail) {
                $flatEntry = $value;
                $flatEntry['detailNr'] = ++$detailNumber;
                foreach ($detail as $detailKey => $detailValue) {
                    $flatEntry[sprintf('detail_%s', $detailKey)] = $detailValue;
                }
                $flatEntry['detail_time'] =
                    TimeCalculator::secondsToHumanReadableTime($flatEntry['detail_timeSpentSeconds']);

                $result[] = $flatEntry;
            }
        }

        return $result;
    }
}
