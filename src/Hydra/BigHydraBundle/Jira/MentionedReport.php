<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\Comments\Mentioned;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Jira\Publish\Csv\Csv;

class MentionedReport
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
     * @param string $mentioned
     * @param array $author
     *
     * @return array
     */
    public function getMentionedInComment($mentioned, array $author)
    {
        $report = new Mentioned($this->mongoRepo);
        $report->setMentionedFilter([$mentioned]);
        $report->setAuthorFilter($author);
        $rawReport = $report->runReport();

        $csv = new Csv();
        $fileName = $this->buildCsvFileName(array_merge(['mentioned', $mentioned, 'by'], $author));
        $csv->saveToFile($fileName, $rawReport);

        return $fileName;
    }

    /**
     * @param array $filters
     *
     * @return string
     */
    protected function buildCsvFileName(array $filters)
    {
        $filename = date("YmdHis");
        $filename .= '_' . implode('_', $filters);
        $filename .= '.csv';

        return $filename;
    }
}
