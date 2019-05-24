<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yousign\YousignClient;

class ClientTest extends TestCase
{
    use TestInit;

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_url_parameter()
    {
        $this->expectException(Exception::class);
        $expectedExceptionMessage = "The config's 'api_url' element is required.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_key' => self::$stagingApiKey
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter as a string
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_url_string_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'api_url' element is not in the form of a string.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_url' => 123456789,
            'api_key' => self::$stagingApiKey
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter long enough
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_url_url_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'api_url' element is not a valid URL.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_url' => 'https:/www.timothecrespy.fr',
            'api_key' => self::$stagingApiKey
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_key_parameter()
    {
        $this->expectException(Exception::class);
        $expectedExceptionMessage = "The config's 'api_key' element is required.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_url' => self::$stagingApiUrl
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter as a string
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_key_string_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'api_key' element is not in the form of a string.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_url' => self::$stagingApiUrl,
            'api_key' => 123456789
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter long enough
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_key_hexadecimal_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'api_key' element is not a valid hexadecimal string.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_url' => self::$stagingApiUrl,
            'api_key' => '123456za'
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client can get users via API call
     * @return void
     */
    public function test_client_can_get_users()
    {
        $config = [
            'api_url' => self::$stagingApiUrl,
            'api_key' => self::$stagingApiKey
        ];
        $yousignClient = new YousignClient($config);

        $users = $yousignClient->getUsers();

        $this->assertObjectHasAttribute('id', $users[ 0 ]);
        $this->assertObjectHasAttribute('firstname', $users[ 0 ]);
        $this->assertObjectHasAttribute('lastname', $users[ 0 ]);
        $this->assertObjectHasAttribute('email', $users[ 0 ]);
        $this->assertObjectHasAttribute('title', $users[ 0 ]);
        $this->assertObjectHasAttribute('phone', $users[ 0 ]);
        $this->assertObjectHasAttribute('status', $users[ 0 ]);
        $this->assertObjectHasAttribute('organization', $users[ 0 ]);
        $this->assertObjectHasAttribute('workspaces', $users[ 0 ]);
    }
}
