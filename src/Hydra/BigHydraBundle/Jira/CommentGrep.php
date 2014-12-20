<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Analyse\Comments\Grep;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;
use Hydra\BigHydraBundle\Jira\Publish\Csv\Csv;

class CommentGrep
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
     * @param string $pattern
     * @param array $author
     *
     * @return array
     */
    public function runGrep($pattern, array $author)
    {
        $report = new Grep($this->mongoRepo);
        $report->setPatternFilter([$pattern]);
        $report->setAuthorFilter($author);
        $rawReport = $report->runReport();

        $csv = new Csv();
        $fileName = $this->buildCsvFileName(array_merge(['commentsWith', $pattern, 'by'], $author));
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
