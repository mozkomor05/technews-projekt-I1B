<?php

require_once(__DIR__ . '/app_load.php');

function bad_request($data = "")
{
    http_response_code(400);
    die($data);
}

$ajax_action = "ajax_" . ($_POST['action'] ?? '');

if (function_exists($ajax_action))
    call_user_func($ajax_action);
else
    bad_request("No such action");

function ajax_process_vote()
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

function comment_for_FE($comment)
{
    /*
     * Není třeba escapovat z bezpečnostních důvodu, protože to dělá jQuery ve funkci text.
     * POZOR: JE TŘEBA escapovat při ukládání (to už by byl příliš velký risk).
     */
    $comment['created_formatted'] = nice_date($comment['created']);
    $comment['email_hash'] = md5($comment['author_email']);

    unset($comment['author_email']);

    return $comment;
}

function ajax_list_comments()
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

    $comments = array_map('comment_for_FE', $comments);

    die(json_encode($comments));
}

function ajax_submit_comment()
{
    global $config, $db;

    $required_vars = [
        'name' => FILTER_SANITIZE_SPECIAL_CHARS,
        'email' => FILTER_VALIDATE_EMAIL,
        'message' => FILTER_SANITIZE_SPECIAL_CHARS,
        'g-recaptcha-response' => FILTER_UNSAFE_RAW,
        'post_id' => FILTER_VALIDATE_INT,
        'reply' => FILTER_VALIDATE_INT
    ];

    $data = filter_input_array(INPUT_POST, $required_vars);
    $ip = $_SERVER['REMOTE_ADDR'];

    foreach ($data as $field)
        if ($field === false)
            bad_request("validation_failure");

    $data['name'] = trim($data['name']);
    $data['message'] = trim($data['message']);
    $name_length = strlen($data['name']);
    $message_length = strlen($data['message']);

    if ($name_length < 3 || $name_length > 40 || $message_length < 1 || $message_length > 2000)
        bad_request("validation_failure");

    if (!checkdnsrr(explode('@', $data['email'])[1] . '.', 'MX'))
        bad_request("email_validation_failure");

    $options = [
        'http' => [
            'method' => 'POST',
            'header' => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
                'secret' => $config->recaptcha->secret,
                'response' => $data['g-recaptcha-response'],
                'remoteip' => $ip
            ])
        ]
    ];
    $context = stream_context_create($options);
    $verification_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $captcha_response = json_decode($verification_response);

    if ($captcha_response->success == false)
        bad_request("captcha_failure");

    $post_exists = $db->queryFirstField("SELECT COUNT(1) FROM posts WHERE id = %i", $data['post_id']);

    if (empty($post_exists))
        bad_request("post_does_not_exist");

    if ($data['reply'] === -1)
        $data['reply'] = null;
    else {
        $reply_exists = $db->queryFirstField("SELECT COUNT(1) FROM comments WHERE comment_id = %i", $data['reply']);

        if (empty($reply_exists))
            bad_request("reply_does_not_exists");
    }

    $inserted = $db->insert('comments', [
        'post_id' => $data['post_id'],
        'author_name' => $data['name'],
        'author_email' => $data['email'],
        'author_ip' => $ip,
        'created' => date("Y-m-d H:i:s"),
        'content' => $data['message'],
        'reply' => $data['reply']
    ]);

    if ($inserted === false)
        bad_request("could_not_insert");

    $comment = comment_for_FE($db->queryFirstRow(
        "SELECT comment_id, reply, author_name, content, created, author_email FROM comments WHERE comment_id = %i LIMIT 1",
        $db->insertId()
    ));

    die(json_encode([
        'comment' => $comment
    ]));
}