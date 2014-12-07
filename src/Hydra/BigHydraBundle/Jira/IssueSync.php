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

    /**
     * @param MongoRepository $repository
     * @param ExtractJiraIssue $extractIssue
     */
    public function __construct(MongoRepository $repository, ExtractJiraIssue $extractIssue)
    {
        $this->repository = $repository;
        $this->extractIssue = $extractIssue;
    }

    /**
     * @return int
     */
    public function sync()
    {
//        $jiraQuery = 'project = INTMPM OR (project = LATAMZ AND labels in (hydra)) ';
        $jiraQuery = 'project = LATAMZ AND labels in (hydra)';
        $fields = 'id,key,summary,updated,comment,worklog';
        $expand = 'changelog,operations';
        $startAt = 0;
        $total = 1;

        while ($startAt < $total) {
            $chunk = $this->extractIssue->retrieveChunk($jiraQuery, $startAt, self::CHUNK_SIZE, $fields, $expand);
//            file_put_contents('getCommentsAndWorklog.php', '<?php ' . var_export($chunk, true));
            $this->save($chunk);

            $startAt += count($chunk['issues']);
            $total = $chunk['total'];
            echo "retrieving issues.. (${startAt}/${total})\n";
        }

        return $startAt;
    }

    /**
     * @param $chunk
     */
    protected function save($chunk)
    {
        $this->repository->saveToMongoDb($chunk['issues']);
    }
}
