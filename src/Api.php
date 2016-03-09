<?php
/**
 * This file contains code about \CloudConvert\Api class
 */
namespace CloudConvert;

use CloudConvert\Exceptions\InvalidParameterException;
use Exception;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\Exception\RequestException;

/**
 * Base Wrapper to manage login and exchanges with CloudConvert API
 *
 * Http connections use guzzle http client api and result of request are
 * object from this http wrapper
 *
 * @package CloudConvert
 * @category CloudConvert
 * @author Josias Montag <josias@montag.info>
 */
class Api
{
    /**
     * Url to communicate with CloudConvert API
     * @var string
     */
    private $endpoint = 'api.cloudconvert.com';
    /**
     * Protocol (http or https) to communicate with CloudConvert API
     * @var string
     */
    private $protocol = 'https';
    /**
     * API Key of the current application
     * @var string
     */
    private $api_key = null;
    /**
     * Contain http client connection
     * @var GuzzleClient
     */
    private $http_client = null;

    /**
     * Construct a new wrapper instance
     *
     * @param string $api_key Key of your application.
     * You can get your API Key on https://cloudconvert.com/user/profile
     * @param GuzzleClient $http_client Instance of http client
     *
     * @throws InvalidParameterException if one parameter is missing or with bad value
     */
    public function __construct($api_key, GuzzleClient $http_client = null)
    {
        if (!isset($api_key)) {
            throw new Exceptions\InvalidParameterException("API Key parameter is empty");
        }
        if (!isset($http_client)) {
            $http_client = new GuzzleClient();
        }
        $this->api_key = $api_key;
        $this->http_client = $http_client;
    }

    /**
     * This is the main method of this wrapper. It will
     * sign a given query and return its result.
     *
     * @param string $method HTTP method of request (GET,POST,PUT,DELETE)
     * @param string $path relative url of API request
     * @param string $content body of the request
     * @param boolean $is_authenticated if the request use authentication
     * @return mixed
     *
     * @throws Exception
     * @throws Exceptions\ApiBadRequestException
     * @throws Exceptions\ApiConversionFailedException
     * @throws Exceptions\ApiException if the CloudConvert API returns an error
     * @throws Exceptions\ApiTemporaryUnavailableException
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     */
    private function rawCall($method, $path, $content = null, $is_authenticated = true)
    {
        $url = $path;
        if (strpos($path, '//') === 0) {
            $url = $this->protocol . ":" . $path;
        } elseif (strpos($url, 'http') !== 0) {
            $url = $this->protocol . '://' . $this->endpoint . $path;
        }

        $options = array(
            'query' => array(),
            'body' => null,
            'headers' => array()
        );


        if (is_array($content) && $method == 'GET') {
            $options['query'] = $content;
        } elseif (gettype($content) == 'resource' && $method == 'PUT') {
            // is upload
            $options['body'] = \GuzzleHttp\Psr7\stream_for($content);

        } elseif (is_array($content)) {
            $body = json_encode($content);
            $options['body'] = \GuzzleHttp\Psr7\stream_for($body);
            $options['headers']['Content-Type'] = 'application/json; charset=utf-8';
        }

        if ($is_authenticated) {
            $options['headers']['Authorization'] = 'Bearer ' . $this->api_key;
        }

        try {
            $response = $this->http_client->request($method, $url, $options);
            if ($response->getHeader('Content-Type') && strpos($response->getHeader('Content-Type')[0], 'application/json') === 0) {
                return json_decode($response->getBody(), true);
            } elseif ($response->getBody()->isReadable()) {
                // if response is a download, return the stream
                return $response->getBody();
            }
        } catch (RequestException $e) {
            if (!$e->getResponse()) {
                throw $e;
            }
            // check if response is JSON error message from the CloudConvert API
            $json = json_decode($e->getResponse()->getBody(), true);

            if (JSON_ERROR_NONE !== json_last_error()) {
                throw new \RuntimeException('Error parsing JSON response');
            }

            if (isset($json['message']) || isset($json['error'])) {
                $msg = isset($json['error']) ? $json['error'] : $json['message'];
                $code = $e->getResponse()->getStatusCode();
                if ($code == 400) {
                    throw new Exceptions\ApiBadRequestException($msg, $code);
                } elseif ($code == 422) {
                    throw new Exceptions\ApiConversionFailedException($msg, $code);
                } elseif ($code == 503) {
                    throw new Exceptions\ApiTemporaryUnavailableException(
                        $msg,
                        $code,
                        $e->getResponse()->getHeader('Retry-After') ? $e->getResponse()->getHeader('Retry-After')[0] : null
                    );
                } else {
                    throw new Exceptions\ApiException($msg, $code);
                }
            } else {
                throw $e;
            }
        }
    }

    /**
     * Wrap call to CloudConvert APIs for GET requests
     *
     * @param string $path path ask inside api
     * @param string $content content to send inside body of request
     * @param boolean $is_authenticated if the request use authentication
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function get($path, $content = null, $is_authenticated = true)
    {
        return $this->rawCall("GET", $path, $content, $is_authenticated);
    }

    /**
     * Wrap call to CloudConvert APIs for POST requests
     *
     * @param string $path path ask inside api
     * @param string $content content to send inside body of request
     * @param boolean $is_authenticated if the request use authentication
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function post($path, $content, $is_authenticated = true)
    {
        return $this->rawCall("POST", $path, $content, $is_authenticated);
    }

    /**
     * Wrap call to CloudConvert APIs for PUT requests
     *
     * @param string $path path ask inside api
     * @param string $content content to send inside body of request
     * @param boolean $is_authenticated if the request use authentication
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function put($path, $content, $is_authenticated = true)
    {
        return $this->rawCall("PUT", $path, $content, $is_authenticated);
    }

    /**
     * Wrap call to CloudConvert APIs for DELETE requests
     *
     * @param string $path path ask inside api
     * @param string $content content to send inside body of request
     * @param boolean $is_authenticated if the request use authentication
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function delete($path, $content = null, $is_authenticated = true)
    {
        return $this->rawCall("DELETE", $path, $content, $is_authenticated);
    }

    /**
     * Get the current API Key
     *
     * @return string
     */
    public function getApiKey()
    {
        return $this->api_key;
    }

    /**
     * Return instance of http client
     *
     * @return GuzzleClient
     */
    public function getHttpClient()
    {
        return $this->http_client;
    }

    /**
     * Create a new Process
     *
     * @param array $parameters Parameters for creating the Process. See https://cloudconvert.com/apidoc#create
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function createProcess($parameters)
    {
        $result = $this->post("/process", $parameters, true);
        return new Process($this, $result['url']);
    }

    /**
     * Shortcut: Create a new Process and start it
     *
     * @param array $parameters Parameters for starting the Process. See https://cloudconvert.com/apidoc#start
     * @return \CloudConvert\Process
     *
     * @throws \CloudConvert\Exceptions\ApiException if the CloudConvert API returns an error
     * @throws \GuzzleHttp\Exception\GuzzleException if there is a general HTTP / network error
     *
     */
    public function convert($parameters)
    {
        $startparameters = $parameters;
        // we don't need the input file for creating the process
        unset($startparameters['file']);
        $process = $this->createProcess($startparameters);
        return $process->start($parameters);
    }
}
