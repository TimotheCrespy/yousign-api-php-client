<?php

declare(strict_types=1);

namespace Tests;

use Exception;
use InvalidArgumentException;
use PHPUnit\Framework\TestCase;
use Yousign\YousignClient;

class MemberTest extends TestCase
{
    use TestInit;

    /**
     * Tests that a client can post a member to a procedure via API call
     * @return void
     */
    public function test_client_can_post_member_to_procedure()
    {
        $config = [
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
        ];
        $yousignClient = new YousignClient($config);

        // Procedure
        $procedure = $yousignClient->postProcedure(
            $name = 'My procedure',
            $description = '',
            $start = false
        );

        $this->assertObjectHasAttribute('id', $procedure);

        // Member
        $member = $yousignClient->postMember(
            $firstname = $this->fakerFirstName(),
            $lastname = $this->fakerLastName(),
            $email = $this->fakerEmail(),
            $phone = $this->fakerPhone(),
            $procedure = $procedure->id
        );

        $this->assertObjectHasAttribute('id', $member);
        $this->assertObjectHasAttribute('firstname', $member);
        $this->assertObjectHasAttribute('lastname', $member);
        $this->assertObjectHasAttribute('email', $member);
        $this->assertObjectHasAttribute('phone', $member);
        $this->assertObjectHasAttribute('status', $member);
        $this->assertObjectHasAttribute('type', $member);
        $this->assertObjectHasAttribute('procedure', $member);

        $this->deleteMember($yousignClient, $member);
    }

    /**
     * Tests that a client can delete a user via API call
     * @return void
     */
    public function test_client_can_delete_member()
    {
        $config = [
            'api_key' => self::$stagingApiKey,
            'is_testing' => true
        ];
        $yousignClient = new YousignClient($config);

        // Procedure
        $procedure = $yousignClient->postProcedure(
            $name = 'My procedure',
            $description = '',
            $start = false
        );

        $this->assertObjectHasAttribute('id', $procedure);

        // Member
        $user = $yousignClient->postMember(
            $firstname = $this->fakerFirstName(),
            $lastname = $this->fakerLastName(),
            $email = $this->fakerEmail(),
            $phone = $this->fakerPhone(),
            $procedure = $procedure->id
        );

        preg_match(YousignClient::UUID_REGEX_BODY, $user->id, $matches);
        $id = $matches[ 0 ];
        
        $response = $yousignClient->deleteMember(
            $id = $id
        );

        $this->assertTrue($response == null);
    }
}
