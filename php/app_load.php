<?php

require_once(__DIR__ . "/../vendor/autoload.php");

require_once(__DIR__ . '/config.php');

require_once(__DIR__ . '/login-tools.php');
require_once(__DIR__ . '/user-tools.php');
require_once(__DIR__ . '/post-tools.php');

require_once(__DIR__ . '/headers.php');
require_once(__DIR__ . '/sidebar.php');

class App
{
    private static ?MeekroDB $db = null;
    private static ?Config $config = null;
    private static ?object $user = null;

    private static bool $loaded = false;

    private function __construct()
    {
    }

    /**
     * @throws Exception
     */
    public static function loadApp()
    {
        if (self::$loaded) {
            throw new Exception("App is already loaded");
        }

        session_start();

        $config = self::$config = Config::loadFromFile(__DIR__ . '/config.json');

        if ($config->get('debug', false)) {
            ini_set('display_errors', '1');
            ini_set('display_startup_errors', '1');
            error_reporting(E_ALL);
        }

        self::$db = new MeekroDB(
            $config->get(['db', 'host']),
            $config->get(['db', 'user']),
            $config->get(['db', 'password']),
            $config->get(['db', 'database']),
            $config->get(['db', 'port'], null),
            $config->get(['db', 'encoding'], 'utf8'),
        );

        self::$user = LoginTools::getUser();
    }

    public static function getConfig(): Config
    {
        return self::$config;
    }

    public static function getDb(): MeekroDB
    {
        return self::$db;
    }

    public static function getUser(): ?object
    {
        return self::$user;
    }

    public static function setUser(?object $user): void
    {
        self::$user = $user;
    }
}

// handle all app errors
set_error_handler(function ($errno, $errstr, $errfile, $errline) {
    throw new ErrorException($errstr, $errno, 0, $errfile, $errline);
});

set_exception_handler(function (Throwable $e) {
    http_response_code(500);
    if (ob_get_length()) {
        ob_clean();
    }
    ?>
    <h1>Error while running app</h1>
    <pre><code><?= htmlspecialchars($e->getMessage()) ?></code></pre>
    <pre><code><?= htmlspecialchars($e->getFile() . ' on line ' . $e->getLine()) ?></code></pre>
    <pre><code><?= htmlspecialchars($e->getTraceAsString()) ?></code></pre>
    <?php
    die();
});


App::loadApp();