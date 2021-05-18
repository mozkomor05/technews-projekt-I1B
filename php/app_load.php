<?php
$config = json_decode(file_get_contents(__DIR__ . '/config.json'));

if ($config->debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require_once(__DIR__ . "/../vendor/autoload.php");

$db = new MeekroDB($config->db->host, $config->db->user, $config->db->password, $config->db->database, null, "utf8");