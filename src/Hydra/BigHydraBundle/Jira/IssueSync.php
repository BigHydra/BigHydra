<?php
namespace Hydra\BigHydraBundle\Jira;

use Hydra\BigHydraBundle\Jira\Extract\ExtractJiraIssue;
use Hydra\BigHydraBundle\Jira\Load\MongoRepository;

class IssueSync
{
    const CHUNK_SIZE = 500;

    /** @var MongoRepository */
    protected $repository;

    /** @var ExtractJiraIssue */
    protected $extractIssue;

    /** @var string */
    protected $queryFilter;

    /**
     * @param MongoRepository $repository
     * @param ExtractJiraIssue $extractIssue
     * @param array $config
     */
    public function __construct(MongoRepository $repository, ExtractJiraIssue $extractIssue, array $config)
    {
        $this->repository = $repository;
        $this->extractIssue = $extractIssue;
        $this->queryFilter = $config['query'];
    }

    /**
     * @return int
     */
    public function sync()
    {
        $jiraQuery = $this->buildQuery(
            $this->queryFilter,
            $this->repository->findLastUpdatedDate()
        );
        $fields = 'id,key,summary,updated,timetracking,comment,worklog';
        $expand = 'changelog,operations';
        $startAt = 0;
        $total = 1;

        while ($startAt < $total) {
            $chunk = $this->extractIssue->retrieveChunk($jiraQuery, $startAt, self::CHUNK_SIZE, $fields, $expand);
            $this->save($chunk);

            $startAt += count($chunk['issues']);
            $total = $chunk['total'];
            echo "retrieved issues.. (${startAt}/${total})\n";
        }

        return $startAt;
    }

    /**
     * @param $chunk
     */
    protected function save($chunk)
    {
        $this->repository->save($chunk['issues']);
    }

    /**
     * @param string $query
     * @param string $lastUpdatedAt
     *
     * @return string
     */
    protected function buildQuery($query, $lastUpdatedAt)
    {
        $jiraQuery = $query;
        if (null !== $lastUpdatedAt) {
            $jiraQuery .= sprintf(" AND updated >= '%s'", $lastUpdatedAt);
        }
        $jiraQuery .= ' ORDER BY updated ASC';

        return $jiraQuery;
    }
}
