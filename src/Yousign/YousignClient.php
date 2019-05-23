<?php

namespace Yousign;

use Exception;
use function GuzzleHttp\json_encode;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;
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
     * The compulsory file extension
     */
    const FILE_EXTENSION = '.pdf';

    /**
     * The regex for id in path
     */
    const UUID_REGEX_STRICT = '/^[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}$/';

    /**
     * The regex for id in body
     */
    const UUID_REGEX = '/[0-9a-f]{8}-([0-9a-f]{4}-){3}[0-9a-f]{12}/';
    
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
     * @param array  $query             The request's query paramters [Optional].
     * @param string $body              The request's method body [Optional].
     * @param array  $additionalHeaders The request's method additional headers [Optional].
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
        } catch (ClientException $e) {
            $this->getLogger()->error(
                'An error occured (sendRequest@YousignClient): ' . $e->getResponse()->getBody()->getContents()
            );
            throw new ClientException($e->getResponse()->getBody(), $request);
        }

        return json_decode($response->getBody());
    }

    /**
     * Checks the request's method.
     *
     * @param string $method The request's method.
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
     * Gets the account's users.
     *
     * @return json
     */
    public function getUsers()
    {
        $method = 'GET';
        $path = '/users';
        return $this->sendRequest($method, $path, [], '', []);
    }

    /**
     * Gets a user from an id.
     *
     * @param string $id The id of the user.
     * @return json
     */
    public function getUser(
        string $id
    ) {
        $method = 'GET';
        $path = '/users';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The user's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        return $this->sendRequest($method, $path, [], '', []);
    }

    /**
     * Creates a user.
     *
     * @param string $firstname The first name of the user.
     * @param string $lastname  The last name of the user.
     * @param string $email     The email address of the user.
     * @param string $phone     The phone number of the user,
     *                          following the E.164 recommendation (https://en.wikipedia.org/wiki/E.164).
     * @return json
     */
    public function postUser(
        string $firstname,
        string $lastname,
        string $email,
        string $phone
    ) {
        $method = 'POST';
        $path = '/users';

        $body = [];

        if (!is_string($firstname)) {
            $message = "The user's first name is not a string.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'firstname' ] = $firstname;

        if (!is_string($lastname)) {
            $message = "The user's first name is not a string.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'lastname' ] = $lastname;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "The user's email address is not valid.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'email' ] = $email;

        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
            $message = "The user's phone number is not valid.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'phone' ] = $phone;

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Deletes a user.
     *
     * @param string $id The id of the user.
     * @return json
     */
    public function deleteUser(
        string $id
    ) {
        $method = 'DELETE';
        $path = '/users';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The user's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        return $this->sendRequest($method, $path, [], '', []);
    }

    /**
     * Gets a procedure's members.
     *
     * @param string $procedure The procedure to retrieve the members on.
     * @return json
     */
    public function getMembers(
        string $procedure
    ) {
        $method = 'GET';
        $path = '/members';

        if (!is_string($procedure) || !preg_match(self::UUID_REGEX, $procedure)) {
            $message = "The procedure's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'procedure' ] = $procedure;

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Creates a member.
     *
     * @param string $firstname The first name of the member.
     * @param string $lastname  The last name of the member.
     * @param string $email     The email address of the member.
     * @param string $phone     The phone number of the member,
     *                          following the E.164 recommendation (https://en.wikipedia.org/wiki/E.164).
     * @param string $procedure The procedure of the member.
     * @return json
     */
    public function postMember(
        string $firstname,
        string $lastname,
        string $email,
        string $phone,
        string $procedure
    ) {
        $method = 'POST';
        $path = '/members';

        $body = [];

        if (!is_string($firstname)) {
            $message = "The user's first name is not a string.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'firstname' ] = $firstname;

        if (!is_string($lastname)) {
            $message = "The user's first name is not a string.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'lastname' ] = $lastname;

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = "The user's email address is not valid.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'email' ] = $email;

        if (!preg_match('/^\+?[1-9]\d{1,14}$/', $phone)) {
            $message = "The user's phone number is not valid.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'phone' ] = $phone;

        if (!is_string($procedure) || !preg_match(self::UUID_REGEX, $procedure)) {
            $message = "The procedure's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'procedure' ] = $procedure;

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Deletes a member.
     *
     * @param string $id The id of the member.
     * @return json
     */
    public function deleteMember(
        string $id
    ) {
        $method = 'DELETE';
        $path = '/members';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The user's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        return $this->sendRequest($method, $path, [], '', []);
    }

    /**
     * Creates a file.
     *
     * @param string $name      The name of the file, with the '.pdf' extension.
     * @param string $content   The base64 of the file, without the base64 header.
     * @param string $type      The type of the file, either 'attachment' or 'signable'.
     * @param string $procedure The procedure for the file [Optional].
     *                          Compulsory if the file type is 'attachment'.
     * @return json
     */
    public function postFile(
        string $name,
        string $content,
        string $type,
        string $procedure = null
    ) {
        $method = 'POST';
        $path = '/files';

        $body = [];

        if (!is_string($name) || substr($name, -strlen(self::FILE_EXTENSION)) !== self::FILE_EXTENSION) {
            $message = "The file's name is not a string ending with '.pdf'.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'name' ] = $name;

        if (base64_encode(base64_decode($content, true)) !== $content) {
            $message = "The file's content is not a base64 string without headers.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'content' ] = $content;

        if (!is_string($type) || ($type != 'signable' && $type != 'attachment')) {
            $message = "The file's type is not a string with either value 'signable' nor 'attachment'.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'type' ] = $type;

        if ($type == 'attachment') {
            if ($procedure == null) {
                $message = "The file's procedure's id is not provided (file type is 'attachment').";
                throw new InvalidArgumentException($message);
            }
            $body[ 'procedure' ] = $procedure;
        }

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Creates a file object.
     *
     * @param string  $file     The file for the file oject.
     * @param string  $member   The member for the file oject.
     * @param integer $page     The number of the page where the signature image will be displayed on the file.
     * @param string  $position The coordinates of the signature image on the page.
     * @param string  $reason   The main information on the signature image.
     * @param string  $mention  The first information on the signature image [Optional].
     * @param string  $mention2 The second information on the signature image [Optional].
     * @return json
     */
    public function postFileObject(
        string $file,
        string $member,
        int $page,
        string $position,
        string $reason,
        string $mention = '',
        string $mention2 = ''
    ) {
        $method = 'POST';
        $path = '/file_objects';

        $body = [];

        if (!is_string($file) || !preg_match(self::UUID_REGEX, $file)) {
            $message = "The file object's file's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'file' ] = $file;

        if (!is_string($file) || !preg_match(self::UUID_REGEX, $member)) {
            $message = "The file object's member's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $body[ 'member' ] = $member;

        $body[ 'page' ] = $page;
        $body[ 'position' ] = $position;
        $body[ 'reason' ] = $reason;
        $body[ 'mention' ] = $mention;
        $body[ 'mention2' ] = $mention2;

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Deletes a file object.
     *
     * @param string $id The id of the file object.
     * @return json
     */
    public function deleteFileObject(
        string $id
    ) {
        $method = 'DELETE';
        $path = '/file_objects';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The file objects's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        return $this->sendRequest($method, $path, [], '', []);
    }

    /**
     * Creates a procedure.
     *
     * @param string  $name        The name of the procedure.
     * @param string  $description The description of the procedure [Optional].
     * @param boolean $start       The status of the procedure, either true or false [Optional].
     * @param array   $members     The members for the procedure [Optional].
     *                             Compulsory if the procedure start is true.
     * @param array   $config      The config for the procedure [Optional].
     * @return json
     */
    public function postProcedure(
        string $name,
        string $description = '',
        bool $start = true,
        array $members = [],
        array $config = []
    ) {
        $method = 'POST';
        $path = '/procedures';

        $body = [];

        $body[ 'name' ] = $name;
        $body[ 'description' ] = $description;
        $body[ 'start' ] = $start;

        if ($start) {
            if ($members == null) {
                $message = "The procedure's member array is not provided (procedure start is true).";
                throw new InvalidArgumentException($message);
            }
            $body[ 'members' ] = $members;
        }

        $body[ 'config' ] = $config;

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Updates a procedure.
     *
     * @param string  $id          The id of the procedure.
     * @param string  $name        The name of the procedure [Optional].
     * @param string  $description The description of the procedure [Optional].
     * @param boolean $start       The status of the procedure, either true or false [Optional].
     * @param array   $members     The members for the procedure [Optional].
     * @param array   $config      The config for the procedure [Optional].
     * @return json
     */
    public function putProcedure(
        string $id,
        string $name = null,
        string $description = null,
        bool $start = null,
        array $members = null,
        array $config = null
    ) {
        $method = 'PUT';
        $path = '/procedures';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The procedure's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        $body = [];

        if (!is_null($name)) {
            $body[ 'name' ] = $name;
        }

        if (!is_null($description)) {
            $body[ 'description' ] = $description;
        }

        if (!is_null($start)) {
            $body[ 'start' ] = $start;
        }

        if (!is_null($members)) {
            $body[ 'members' ] = $members;
        }

        if (!is_null($config)) {
            $body[ 'config' ] = $config;
        }

        $body = json_encode($body);

        return $this->sendRequest($method, $path, [], $body, []);
    }

    /**
     * Deletes a procedure.
     *
     * @param string $id The id of the procedure.
     * @return json
     */
    public function deleteProcedure(
        string $id
    ) {
        $method = 'DELETE';
        $path = '/procedures';

        if (!is_string($id) || !preg_match(self::UUID_REGEX_STRICT, $id)) {
            $message = "The user's id is not a valid UUID.";
            throw new InvalidArgumentException($message);
        }
        $path .= '/' . $id;

        return $this->sendRequest($method, $path, [], '', []);
    }
}
