<?php

require_once(__DIR__ . '/app_load.php');

function bad_response() {
    http_response_code(400);
    die();
}

$ajax_action = $_POST['action'] ?? '';

if (function_exists($ajax_action))
    call_user_func($ajax_action);
else
    bad_response();

function process_post_vote() {

}