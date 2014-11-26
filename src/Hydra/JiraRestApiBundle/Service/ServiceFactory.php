<?php
namespace Hydra\JiraRestApiBundle\Service;

class ServiceFactory
{
    /**
     * @param string $host
     * @param string $username
     * @param string $password
     *
     * @return \Hydra\JiraRestApiBundle\Service\IssueService
     */
    public function getIssueService($host, $username, $password)
    {
        return new IssueService($host, $username, $password);
    }
}
