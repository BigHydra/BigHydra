<?php
namespace Hydra\BigHydraBundle\Jira\Publish\Csv;

use Hydra\BigHydraBundle\Library\TimeCalculator;

class ByAuthorAndDayCsv
{
    /**
     * @param array $rawReport
     *
     * @return array
     */
    public function flatten(array $rawReport)
    {
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
}
