<?php
namespace Hydra\JiraRestApiBundle\Service;

class IssueService extends AbstractService
{
    /**
     * @param string $query
     * @param int $startAt
     * @param int $maxResult
     * @param string $fields
     * @param string $expand, i.e. editmeta,renderedFields,transitions,changelog,operations
     *
     * @return array
     */
    public function search($query, $startAt, $maxResult, $fields, $expand)
    {
        $searchParams = array(
            "jql"        => $query,
            "startAt"    => $startAt,
            "maxResults" => $maxResult,
            "fields"     => $fields,
            "expand"     => $expand,
        );
        $rawResult = $this->doRequest("search", 'GET', $searchParams);

        return $rawResult;
    }

    /**
     * @param string $issueNumber
     *
     * @return array
     */
    public function issue($issueNumber)
    {
        $rawResult = $this->doRequest("issue/$issueNumber", 'GET', array());
        return $rawResult;
    }

    /**
     * @param string $issueNumber
     *
     * @return array
     */
    public function issueComment($issueNumber)
    {
        $rawResult = $this->doRequest("issue/$issueNumber/comment/", 'GET', array());
        return $rawResult;
    }

    /**
     * @param string $issueNumber
     *
     * @return array
     */
    public function issueWorklog($issueNumber)
    {
        $rawResult = $this->doRequest("issue/$issueNumber/worklog", 'GET', array());
        return $rawResult;
    }
}
