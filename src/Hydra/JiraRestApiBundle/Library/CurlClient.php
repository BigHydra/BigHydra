<?php
/*
 * The MIT License
 *
 * Copyright (c) 2012 Shuhei Tanuma
 *
 * Permission is hereby granted, free of charge, to any person obtaining a copy
 * of this software and associated documentation files (the "Software"), to deal
 * in the Software without restriction, including without limitation the rights
 * to use, copy, modify, merge, publish, distribute, sublicense, and/or sell
 * copies of the Software, and to permit persons to whom the Software is
 * furnished to do so, subject to the following conditions:
 *
 * The above copyright notice and this permission notice shall be included in
 * all copies or substantial portions of the Software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL THE
 * AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING FROM,
 * OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER DEALINGS IN
 * THE SOFTWARE.
 *
 * https://github.com/chobie/jira-api-restclient/blob/master/src/Jira/Api/Client/CurlClient.php
 */
namespace Hydra\JiraRestApiBundle\Library;

class CurlClient
{
    protected $username;
    protected $password;

    /**
     * create a traditional php client
     */
    public function __construct($username, $password)
    {
        $this->username = $username;
        $this->password = $password;
    }

    /**
     * send request to the api server
     *
     * @param $method
     * @param $url
     * @param array $data
     * @param $host
     * @param bool $isFile
     * @param bool $debug
     *
     * @throws \Exception
     * @return array|string
     */
    public function sendRequest($method, $url, $data, $host, $isFile = false, $debug = false)
    {
        $host .= 'rest/api/2/';
        $curl = curl_init();

        if (('GET' === $method)) {
            $url .= "?" . http_build_query($data);
        }

//        var_dump($host . $url);
        curl_setopt($curl, CURLOPT_URL, $host . $url);
        curl_setopt($curl, CURLOPT_HEADER, 0);
        curl_setopt($curl, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($curl, CURLOPT_USERPWD, sprintf("%s:%s", $this->username, $this->password));
        curl_setopt($curl, CURLOPT_HTTP_VERSION, CURL_HTTP_VERSION_1_0);
        curl_setopt($curl, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($curl, CURLOPT_SSL_VERIFYHOST, 0);
        curl_setopt($curl, CURLOPT_VERBOSE, $debug);
        if ($isFile) {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array('X-Atlassian-Token: nocheck'));
        } else {
            curl_setopt($curl, CURLOPT_HTTPHEADER, array("Content-Type: application/json;charset=UTF-8"));
        }
        if ($method == "POST") {
            curl_setopt($curl, CURLOPT_POST, 1);
            if ($isFile) {
                curl_setopt($curl, CURLOPT_POSTFIELDS, $data);
            } else {
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        } else {
            if ($method == "PUT") {
                curl_setopt($curl, CURLOPT_CUSTOMREQUEST, "PUT");
                curl_setopt($curl, CURLOPT_POSTFIELDS, json_encode($data));
            }
        }

//        var_dump($url);
        $data = curl_exec($curl);

        $errorNumber = curl_errno($curl);
        if ($errorNumber > 0) {
            throw new \Exception(
                sprintf('Jira request failed: code = %s, "%s"', $errorNumber, curl_error($curl))
            );
        }
        // if empty result and status != "204 No Content"
        if (curl_getinfo($curl, CURLINFO_HTTP_CODE) == 401) {
            throw new \Exception("Unauthorized");
        }
        if ($data === '' && curl_getinfo($curl, CURLINFO_HTTP_CODE) != 204) {
            throw new \Exception("JIRA Rest server returns unexpected result.");
        }

        if (is_null($data)) {
            throw new \Exception("JIRA Rest server returns unexpected result.");
        }

        return $data;
    }
}
