<?php
namespace Hydra\BigHydraBundle\Jira\Publish\Csv;

use Hydra\BigHydraBundle\Library\TimeCalculator;

class ByAuthorTicketAndDayCsv extends Csv
{
    /**
     * @param array $rawReport
     *
     * @return array
     */
    public function flatten(array $rawReport)
    {
        /**
         * $rawReport structure
         * [author] =>
         * [issue] =>
         * [summary] =>
         * [year] =>
         * [month] =>
         * [day] =>
         * [details] => Array
         * (
         * [0] => Array
         * (
         *   [comment] =>
         *   [startedDate] =>
         *   [timeSpentSeconds] =>
         * )
         * [time] =>
         */

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
