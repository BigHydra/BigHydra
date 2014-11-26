<?php
namespace Hydra\JiraRestApiBundle\Service;

use Hydra\JiraRestApiBundle\Library\CurlClient;

/**
 *
 */
abstract class AbstractService
{
    protected $host;
    protected $username;
    protected $password;

    /**
     * @param string $host
     * @param string $username
     * @param string $password
     */
    public function __construct($host, $username, $password)
    {
        $this->host = $host;
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * @return bool
     */
    public function testConnection()
    {
        return 0 < count($this->getUser($this->username));
    }

    /**
     * @param string $username
     *
     * @return array
     */
    public function getUser($username)
    {
        $result = $this->doRequest('user/search/', 'GET', array('username' => $username));
        return $result;
    }

    /**
     * @param string $url
     * @param string $method GET|POST
     * @param array $params
     *
     * @return array
     * @throws \Exception
     */
    protected function doRequest($url, $method, $params)
    {
        $client = new CurlClient($this->username, $this->password);
        $response = $client->sendRequest($method, $url, $params, $this->host);
//        var_dump(array($method, $url, $params, $this->host));
        $decodedResponse = json_decode($response, true);
        if (isset($decodedResponse['errorMessages'])) {
            print_r($decodedResponse);
            throw new \Exception(__METHOD__ . "($url, $method, $params) failed");
        }

        return $decodedResponse;
    }
}
