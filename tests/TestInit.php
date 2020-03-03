<?php

namespace Tests;

use Dotenv\Dotenv;
use Faker\Factory as FakerFactory;
use Yousign\YousignClient;

trait TestInit
{
    private static $stagingApiUrl = '';
    private static $stagingApiKey = '';

    private static $faker;

    /**
     * PHPUnit setup
     *
     * @return void
     */
    public static function setUpBeforeClass(): void
    {
        if (file_exists(dirname(__DIR__, 1) . '/.env')) {
            $dotenv = Dotenv::create(dirname(__DIR__, 1));
            $dotenv->load();
        }
        self::$stagingApiUrl = getenv('YOUSIGN_STAGING_API_URL');
        self::$stagingApiKey = getenv('YOUSIGN_STAGING_API_KEY');

        self::$faker = FakerFactory::create('fr_FR');
    }

    /**
     * Generate a random first name
     *
     * @return string
     */
    private function fakerFirstName()
    {
        return self::$faker->firstName;
    }

    /**
     * Generate a random last name
     *
     * @return string
     */
    private function fakerLastName()
    {
        return self::$faker->lastName;
    }

    /**
     * Generate a random email address
     *
     * @return string
     */
    private function fakerEmail()
    {
        return self::$faker->regexify('[a-z]{20}@gmail\.com');
        $phone = self::$faker->regexify('\+336[0-9]{8}');
    }

    /**
     * Generate a random E164 phone number
     *
     * @return string
     */
    private function fakerPhone()
    {
        return self::$faker->regexify('\+336[0-9]{8}');
    }

    /**
     * Deletes a user after a test
     *
     * @param YousignClient $yousignClient The Yousign client.
     * @param object|json   $user          The user to delete.
     * @return void
     */
    private function deleteUser(YousignClient $yousignClient, $user)
    {
        $yousignClient->getLogger()->info('Deleting user with id: ' . $user->id);

        preg_match(YousignClient::UUID_REGEX, $user->id, $matches);
        $id = $matches[0];

        $response = $yousignClient->deleteUser(
            $id = $id
        );
    }

    /**
     * Deletes a member after a test
     *
     * @param YousignClient $yousignClient The Yousign client.
     * @param object|json   $member        The member to delete.
     * @return void
     */
    private function deleteMember(YousignClient $yousignClient, $member)
    {
        $yousignClient->getLogger()->info('Deleting member with id: ' . $member->id);

        preg_match(YousignClient::UUID_REGEX, $member->id, $matches);
        $id = $matches[0];

        $response = $yousignClient->deleteMember(
            $id = $id
        );
    }

    /**
     * Deletes a file object after a test
     *
     * @param YousignClient $yousignClient The Yousign client.
     * @param object|json   $fileObject    The file object to delete.
     * @return void
     */
    private function deleteFileObject(YousignClient $yousignClient, $fileObject)
    {
        $yousignClient->getLogger()->info('Deleting file object with id: ' . $fileObject->id);

        preg_match(YousignClient::UUID_REGEX, $fileObject->id, $matches);
        $id = $matches[0];

        $response = $yousignClient->deleteFileObject(
            $id = $id
        );
    }
}
