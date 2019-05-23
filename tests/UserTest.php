<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yousign\YousignClient;

class UserTest extends TestCase
{
    use TestInit;

    /**
     * Tests that a client can post a user via API call
     * @return void
     */
    public function test_client_can_post_user()
    {
        $config = [
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
        ];
        $yousignClient = new YousignClient($config);

        $user = $yousignClient->postUser(
            $firstname = $this->fakerFirstName(),
            $lastname = $this->fakerLastName(),
            $email = $this->fakerEmail(),
            $phone = $this->fakerPhone()
        );

        $this->assertObjectHasAttribute('id', $user);
        $this->assertObjectHasAttribute('firstname', $user);
        $this->assertObjectHasAttribute('lastname', $user);
        $this->assertObjectHasAttribute('email', $user);
        $this->assertObjectHasAttribute('phone', $user);
        $this->assertObjectHasAttribute('status', $user);
        $this->assertObjectHasAttribute('permission', $user);

        $this->deleteUser($yousignClient, $user);
    }

    /**
     * Tests that a client can get a user via API call
     * @return void
     */
    public function test_client_can_get_user()
    {
        $config = [
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
        ];
        $yousignClient = new YousignClient($config);

        $user = $yousignClient->postUser(
            $firstname = $this->fakerFirstName(),
            $lastname = $this->fakerLastName(),
            $email = $this->fakerEmail(),
            $phone = $this->fakerPhone()
        );

        preg_match(YousignClient::UUID_REGEX, $user->id, $matches);
        $id = $matches[ 0 ];

        $user = $yousignClient->getUser(
            $id = $id
        );

        $this->assertObjectHasAttribute('id', $user);
        $this->assertObjectHasAttribute('firstname', $user);
        $this->assertObjectHasAttribute('lastname', $user);
        $this->assertObjectHasAttribute('email', $user);
        $this->assertObjectHasAttribute('phone', $user);
        $this->assertObjectHasAttribute('status', $user);
        $this->assertObjectHasAttribute('permission', $user);
        $this->assertObjectHasAttribute('deleted', $user);

        $this->assertTrue($user->deleted == false);

        $this->deleteUser($yousignClient, $user);
    }

    /**
     * Tests that a client can delete a user via API call
     * @return void
     */
    public function test_client_can_delete_user()
    {
        $config = [
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
        ];
        $yousignClient = new YousignClient($config);

        $user = $yousignClient->postUser(
            $firstname = $this->fakerFirstName(),
            $lastname = $this->fakerLastName(),
            $email = $this->fakerEmail(),
            $phone = $this->fakerPhone()
        );

        preg_match(YousignClient::UUID_REGEX, $user->id, $matches);
        $id = $matches[ 0 ];
        
        $response = $yousignClient->deleteUser(
            $id = $id
        );

        $this->assertTrue($response == null);
    }
}
