<?php
namespace Hydra\BigHydraBundle\Jira\Extract;

use Hydra\JiraRestApiBundle\Service\IssueService;

class ExtractJiraIssue
{
    /** @var IssueService */
    protected $jiraService;

    /**
     * @param IssueService $jiraService
     */
    public function __construct(IssueService $jiraService)
    {
        $this->jiraService = $jiraService;
    }

    /**
     * @param string $jiraQuery
     * @param int $startAt
     * @param int $maxResult
     * @param string $fields
     * @param string $expand
     *
     * @return array[]
     */
    public function retrieveChunk($jiraQuery, $startAt, $maxResult, $fields, $expand)
    {
        $issues = $this->jiraService->search($jiraQuery, $startAt, $maxResult, $fields, $expand);

        foreach ($issues['issues'] as &$issue) {
            if (isset($issue['fields']['worklog']) && isset($issue['fields']['worklog']['worklogs'])) {
                if ($issue['fields']['worklog']['total'] > 20) {
                    $fullWorklog = $this->jiraService->issueWorklog($issue['key']);
//                    file_put_contents('fullWorklog.php','<?php return '. var_export($fullWorklog, true));
//                    exit;
                    $issue['fields']['worklog'] = $fullWorklog;
                }
            }
        };

        return $issues;
    }
}
