<?php

require_once(__DIR__ . '/app_load.php');

function bad_request()
{
    http_response_code(400);
    die();
}

$ajax_action = $_POST['action'] ?? '';

if (function_exists($ajax_action))
    call_user_func($ajax_action);
else
    bad_request();

function process_vote()
{
    global $db;
    header('Content-Type: application/json');
    $ip = $_SERVER['REMOTE_ADDR'];
    $post_id = $_POST['post_id'] ?? null;
    $vote = $_POST['vote'] ?? null;
    $type = $_POST['type'] ?? null;

    if (empty($vote) || empty($post_id) || empty($type))
        bad_request();

    $vote = intval($vote);

    if (!in_array($type, ['post', 'comment']) || !in_array($vote, [1, -1]))
        bad_request();

    $votes_cookie = json_decode($_COOKIE['votes'] ?? '[]');
    $voted_ip = $db->queryFirstField("
        SELECT COUNT(1)
        FROM karma
        WHERE obj_id = %i AND ip = %s AND type = 'post'
    ", $post_id, $ip);

    if (in_array($post_id, $votes_cookie) || $voted_ip != 0)
        die(json_encode([
            'success' => false
        ]));

    $db->insert('karma', [
        'obj_id' => $post_id,
        'type' => $type,
        'value' => $vote,
        'ip' => $ip
    ]);

    $votes_cookie[] = $post_id;
    setcookie("votes", json_encode($votes_cookie), 2147483647, '/');

    die(json_encode([
        'success' => true
    ]));
}

function list_comments()
{
    global $db;

    $post_id = $_POST['post_id'] ?? null;

    if (empty($post_id))
        bad_request();

    /*
     * Bohužel je třeba získat vše najednou. Abych limitoval počet komentářů, musel bych limitovat počet parentů a
     * k nim rekurizvně dohlédávat childy přes CTE. To se mi podařilo, ale implementace byla zbytečně náročná.
     */
    $comments = $db->query(
        "SELECT comment_id, reply, author_name, content, created, author_email FROM comments WHERE post_id = %i ORDER BY created DESC",
        $post_id
    );

    $comments = array_map(function ($comment) {
        $comment['created_formatted'] = nice_date($comment['created']);
        $comment['email_hash'] = md5($comment['author_email']);
        unset($comment['author_email']);
        return $comment;
    }, $comments);

    die(json_encode($comments));
}