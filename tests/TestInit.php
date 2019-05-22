<?php

namespace Tests;

use Dotenv\Dotenv;

trait TestInit
{
    /**
     * PHPUnit setup
     *
     * @return void
     */
    public static function setUpBeforeClass()
    {
        $dotenv = Dotenv::create(dirname(__DIR__, 1));
        $dotenv->load();
        self::$stagingApiKey = getenv('YOUSIGN_STAGING_API_KEY');
    }
}
