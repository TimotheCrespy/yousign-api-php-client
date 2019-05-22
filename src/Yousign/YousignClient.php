<?php

namespace Yousign;

use Exception;
use GuzzleHttp\Client;
use GuzzleHttp\Psr7\Request;
use InvalidArgumentException;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;
use Psr\Log\LoggerInterface;

/**
 * Lets the developer make API calls to the Yousign REST API.
 *
 * Usage example (production):
 *
 *   $yousignClient = new YousignApi([
 *       'api_key' => '[YOUR_API_KEY]'
 *   ]);
 *   $users = $yousignClient->getUsers();
 *
 * Usage example (test):
 *
 *   $yousignClient = new YousignApi([
 *       'api_key' => '[YOUR_STAGING_API_KEY]',
 *       'is_testing' => true
 *   ]);
 *   $users = $yousignClient->getUsers();
 *
 * @author Timothé Crespy <contact@timothecrespy.fr>
 */
class YousignClient
{
    /**
     * The API base production URI
     */
    const API_BASE_PATH = 'https://api.yousign.com';

    /**
     * The API base staging (test) URI
     */
    const API_BASE_PATH_TEST = 'https://staging-api.yousign.com';
    
    /**
     * @var string $baseUri The API base URI
     */
    private $baseUri = '';

    /**
     * @var string $apiKey The API key
     */
    private $apiKey = '';
    
    /**
     * @var array $baseHeaders The base headers to access the Yousign API
     */
    private $baseHeaders = [];

    /**
     * @var Psr\Log\LoggerInterface $logger
     */
    private $logger;

    /**
     * Constructor
     *
     * @param array $config The configuration elements.
     * @return void
     */
    public function __construct(array $config)
    {
        $this->initialiseEnvironment($config);
    }
    
    /**
     * Initialise environment variables
     *
     * @param array $config The configuration elements.
     * @return boolean
     */
    private function initialiseEnvironment(array $config)
    {
        $this->checkConfigApiKey($config);

        $this->checkConfigIsTesting($config);
        
        $this->baseHeaders = [
            'Authorization' => 'Bearer ' . $this->apiKey,
            'Content-type' => 'application/json'
        ];

        return true;
    }

    /**
     * Checks the config's 'api_key' element.
     *
     * @param array $config The config array.
     * @return boolean
     */
    private function checkConfigApiKey(array $config)
    {
        if (isset($config[ 'api_key' ])) {
            $api_key = $config[ 'api_key' ];
            if (!is_string($api_key)) {
                $message = "The config's 'api_key' element is not in the form of a string.";
                throw new InvalidArgumentException($message);
            } elseif (strlen($api_key) < 8) {
                $message = "The config's 'api_key' element does not have enough characters.";
                throw new InvalidArgumentException($message);
            }
        } else {
            $message = "The config's 'api_key' element is required.";
            throw new Exception($message);
        }

        $this->apiKey = $api_key;

        return true;
    }

    /**
     * Checks the config's 'is_testing' element, and sets the $baseUri variable accordingly
     *
     * @param array $config The config array.
     * @return boolean
     */
    private function checkConfigIsTesting(array $config)
    {
        if (isset($config[ 'is_testing' ])) {
            $is_testing = $config[ 'is_testing' ];
            if (!is_bool($is_testing)) {
                $message = "The config's 'is_testing' element is not in the form of a boolean.";
                throw new InvalidArgumentException($message);
            } else {
                $this->baseUri = self::API_BASE_PATH_TEST;
            }
        } else {
            $this->baseUri = self::API_BASE_PATH;
        }

        return true;
    }

    /**
     * Sets the Logger object
     *
     * @param Psr\Log\LoggerInterface $logger The Logger object.
     * @return void
     */
    public function setLogger(LoggerInterface $logger)
    {
        $this->logger = $logger;
    }

    /**
     * Gets the Logger object
     *
     * @return Psr\Log\LoggerInterface The LoggerInterface implementation.
     */
    public function getLogger()
    {
        if (!isset($this->logger)) {
            $this->logger = $this->createDefaultLogger();
        }
        return $this->logger;
    }

    /**
     * Creates a default Logger implementation
     *
     * @return Psr\Log\Logger The Logger implementation
     */
    protected function createDefaultLogger()
    {
        $logger = new Logger('yousign-api-php-client');
        $handler = new StreamHandler(dirname(__FILE__, 3) . '/monolog.log', Logger::DEBUG);
        $logger->pushHandler($handler);
        return $logger;
    }
    
    /**
     * Triggers an HTTP request.
     *
     * @param string $method            The request's method.
     * @param string $path              The request's path.
     * @param array  $query             The request's query paramters.
     * @param string $body              The request's method body.
     * @param array  $additionalHeaders The request's method additional headers.
     *
     * @return json
     */
    private function sendRequest(
        string $method,
        string $path,
        array $query = [],
        string $body = '',
        array $additionalHeaders = []
    ) {
        try {
            $this->checkRequestMethod($method);
            $this->checkRequestPath($path);
            $this->checkRequestQuery($query);
            $this->checkRequestBody($body);
            $this->checkRequestAdditionalHeaders($additionalHeaders);

            $method = strtoupper($method);

            $headers = array_merge(
                $this->baseHeaders,
                $additionalHeaders
            );
            
            $client = new Client([
                'base_uri' => $this->baseUri
            ]);

            $request = new Request(
                $method,
                $path,
                $headers,
                $body
            );

            $response = $client->send($request, [
                'query' => $query
            ]);
        } catch (Exception $e) {
            $this->getLogger()->error(
                'An error occured (sendRequest@YousignClient): ',
                $e->getMessage()
            );
            throw new Exception($e->getMessage());
        }

        return json_decode($response->getBody());
    }

    /**
     * Checks the request's method.
     *
     * @param string $method The request's method.
     *
     * @return boolean
     */
    private function checkRequestMethod(string $method)
    {
        $method = strtoupper($method);
        $availableMethods = [
            'GET',
            'POST',
            'PUT',
            'HEAD',
            'DELETE',
            'PATCH',
            'OPTIONS',
        ];

        if (!in_array($method, $availableMethods)) {
            $message = "The request's method is unknown.";
            throw new Exception($message);
        }

        return true;
    }

    /**
     * Checks the request's path.
     *
     * @param string $path The request's path.
     *
     * @return boolean
     */
    private function checkRequestPath(string $path)
    {
        if (!substr($path, 0, strlen($path)) == '/') {
            $message = "The request's path does not start with a '/' character.";
            throw new Exception($message);
        }

        if (strlen($path) <= 1) {
            $message = "The request's path does not have enough characters (besides the '/' character).";
            throw new Exception($message);
        }

        return true;
    }

    /**
     * Checks the request's query.
     *
     * @param array $query The request's query parameters.
     *
     * @return boolean
     */
    private function checkRequestQuery(array $query)
    {
        if (!is_array($query)) {
            $message = "The request's query parameters are not in the form of an array.";
            throw new Exception($message);
        }
        
        return true;
    }

    /**
     * Checks the request's body.
     *
     * @param string $body The request's body.
     *
     * @return boolean
     */
    private function checkRequestBody(string $body)
    {
        if (!is_string($body)) {
            $message = "The request's body is not in the form of a string.";
            throw new Exception($message);
        }

        return true;
    }

    /**
     * Checks the request's additional headers.
     *
     * @param array $additionalHeaders The request's additional headers.
     *
     * @return boolean
     */
    private function checkRequestAdditionalHeaders(array $additionalHeaders)
    {
        if (!is_array($additionalHeaders)) {
            $message = "The request's additional headers are not in the form of an array.";
            throw new Exception($message);
        }

        return true;
    }

    /**
     * Get the account's users.
     *
     * @return json
     */
    public function getUsers()
    {
        $method = 'GET';
        $path = '/users';
        return $this->sendRequest($method, $path);
    }
}
