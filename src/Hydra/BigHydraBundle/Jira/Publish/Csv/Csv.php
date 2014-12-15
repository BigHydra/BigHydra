<?php
namespace Hydra\BigHydraBundle\Jira\Publish\Csv;

class Csv
{
    /**
     * @param string $filename
     * @param array $report
     */
    public function saveToFile($filename, array $report)
    {
        $fp = fopen($filename, 'w');
        fputcsv($fp, array_keys(current($report)));
        foreach ($report as $line) {
            fputcsv($fp, $line);
        }
        fclose($fp);
    }
}
