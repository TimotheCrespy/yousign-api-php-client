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
    public function test_client_cannot_be_instanciated_without_config_api_key_parameter()
    {
        $this->expectException(Exception::class);
        $expectedExceptionMessage = "The config's 'api_key' element";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [];
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
            'api_key' => 123456789
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client cannot be instanciated without the $config[ 'api_key' ] parameter long enough
     * @return void
     */
    public function test_client_cannot_be_instanciated_without_config_api_key_long_parameter()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'api_key' element does not have enough characters.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_key' => '123456'
        ];
        $yousignClient = new YousignClient($config);
    }

    /**
     * Tests that a client can be instanciated only with the $config[ 'is_testing' ] parameter as a boolean
     * @return void
     */
    public function test_client_can_be_instanciated_only_with_config_is_testing_parameter_boolean()
    {
        $this->expectException(InvalidArgumentException::class);
        $expectedExceptionMessage = "The config's 'is_testing' element is not in the form of a boolean.";
        $this->expectExceptionMessage($expectedExceptionMessage);

        $config = [
            'api_key' => '123456789',
            'is_testing' => 'true'
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
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
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
