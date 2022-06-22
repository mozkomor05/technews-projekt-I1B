<?php

require_once(__DIR__ . '/app_load.php');

function bad_request($data = "")
{
    http_response_code(400);
    die($data);
}

$ajax_action = "ajax_" . ($_POST['action'] ?? '');

if (function_exists($ajax_action)) {
    call_user_func($ajax_action);
} else {
    bad_request("No such action");
}

function validate_captcha($captcha_response, $ip)
{
    $options = [
        'http' => [
            'method'  => 'POST',
            'header'  => "Content-Type: application/x-www-form-urlencoded\r\n",
            'content' => http_build_query([
                'secret'   => App::getConfig()->get(['recaptcha', 'secret']),
                'response' => $captcha_response,
                'remoteip' => $ip,
            ]),
        ],
    ];

    $context               = stream_context_create($options);
    $verification_response = file_get_contents('https://www.google.com/recaptcha/api/siteverify', false, $context);
    $verification_response = json_decode($verification_response);

    if ( ! $verification_response->success) {
        bad_request("captcha_failure");
    }

    return true;
}

function ajax_process_vote()
{
    $db = App::getDb();

    $ip      = $_SERVER['REMOTE_ADDR'];
    $post_id = $_POST['post_id'] ?? null;
    $vote    = $_POST['vote'] ?? null;
    $type    = $_POST['type'] ?? null;

    header('Content-Type: application/json');

    if (empty($vote) || empty($post_id) || empty($type)) {
        bad_request();
    }

    $vote = intval($vote);

    if ( ! in_array($type, ['post', 'comment']) || ! in_array($vote, [1, -1])) {
        bad_request();
    }

    $votes_cookie = json_decode($_COOKIE['votes'] ?? '[]');
    $voted_ip     = $db->queryFirstField(
        "SELECT COUNT(1)
        FROM karma
        WHERE obj_id = %i AND ip = %s AND type = 'post'",
        $post_id,
        $ip
    );

    if (in_array($post_id, $votes_cookie) || $voted_ip != 0) {
        die(
        json_encode([
            'success' => false,
        ])
        );
    }

    $db->insert('karma', [
        'obj_id' => $post_id,
        'type'   => $type,
        'value'  => $vote,
        'ip'     => $ip,
    ]);

    $votes_cookie[] = $post_id;
    setcookie("votes", json_encode($votes_cookie), 2147483647, '/');

    die(
    json_encode([
        'success' => true,
    ])
    );
}

function comment_for_FE($comment)
{
    $currentUser                  = LoginTools::getUser();
    $userName                     = $comment['author_user'] ?: false;
    $comment['created_formatted'] = PostTools::getNiceDate($comment['created']);
    $comment['is_user']           = (bool)$userName;
    $comment['can_edit']          = $comment['is_user'] && ! empty($currentUser) && $userName === $currentUser->user_name;

    if ($userName) {
        $user                   = UserTools::fetchUser($userName);
        $comment['author_name'] = UserTools::getNiceName($user) . sprintf(
                ' <small class="comment-username">(<a href="/Profily/%s">@<i>%s<i></a>)</small>',
                $user->user_name,
                $user->user_name
            );
        $comment['image_src']   = UserTools::getAvatar($user);
    } else {
        $comment['image_src'] = 'https://www.gravatar.com/avatar/' . md5($comment['author_email']) . '?s=64&d=wavatar';
    }

    unset($comment['author_email']);
    unset($comment['author_user']);

    return $comment;
}

function ajax_list_comments()
{
    $db      = App::getDb();
    $post_id = $_POST['post_id'] ?? null;

    if (empty($post_id)) {
        bad_request();
    }

    /*
     * Bohužel je třeba získat vše najednou. Abych limitoval počet komentářů, musel bych limitovat počet parentů a
     * k nim rekurizvně dohlédávat childy přes CTE. To se mi podařilo, ale implementace byla zbytečně náročná.
     */
    $comments = $db->query(
        "SELECT comment_id, reply, author_name, content, created, author_email, author_user FROM comments WHERE post_id = %i ORDER BY created DESC",
        $post_id
    );

    $comments = array_map('comment_for_FE', $comments);

    die(json_encode($comments));
}

function ajax_submit_comment()
{
    $db            = App::getDb();
    $loggedIn      = LoginTools::isLoggedIn();
    $user          = LoginTools::getUser();
    $required_vars = [
        'message' => FILTER_SANITIZE_SPECIAL_CHARS,
        'post_id' => FILTER_VALIDATE_INT,
        'reply'   => FILTER_VALIDATE_INT,
    ];

    if ( ! $loggedIn) {
        $required_vars['email']                = FILTER_VALIDATE_EMAIL;
        $required_vars['name']                 = FILTER_SANITIZE_SPECIAL_CHARS;
        $required_vars['g-recaptcha-response'] = FILTER_UNSAFE_RAW;
    }

    $data = filter_input_array(INPUT_POST, $required_vars);
    $ip   = $_SERVER['REMOTE_ADDR'];

    foreach ($data as $field) {
        if ($field === false || $field === null) {
            bad_request("validation_failure");
        }
    }

    $name_length = false;

    if ( ! $loggedIn) {
        $data['name'] = trim($data['name']);
        $name_length  = strlen($data['name']);
    }

    $data['message'] = trim($data['message']);
    $message_length  = strlen($data['message']);

    if (( ! $loggedIn && $name_length < 3 || $name_length > 40) || $message_length < 1 || $message_length > 2000) {
        bad_request("validation_failure");
    }

    if ( ! $loggedIn) {
        if ( ! checkdnsrr(explode('@', $data['email'])[1] . '.', 'MX')) {
            bad_request("email_validation_failure");
        }
        validate_captcha($data['g-recaptcha-response'], $ip);
    }

    $post_exists = $db->queryFirstField("SELECT COUNT(1) FROM posts WHERE id = %i", $data['post_id']);

    if (empty($post_exists)) {
        bad_request("post_does_not_exist");
    }

    if ($data['reply'] === -1) {
        $data['reply'] = null;
    } else {
        $reply_exists = $db->queryFirstField("SELECT COUNT(1) FROM comments WHERE comment_id = %i", $data['reply']);

        if (empty($reply_exists)) {
            bad_request("reply_does_not_exists");
        }
    }

    $data['message'] = str_replace('&#10;', '<br />', $data['message']);

    $inserted = $db->insert('comments', [
        'post_id'      => $data['post_id'],
        'author_user'  => ! $loggedIn ? null : $user->user_name,
        'author_name'  => ! $loggedIn ? $data['name'] : UserTools::getNiceName($user),
        'author_email' => ! $loggedIn ? $data['email'] : $user->email,
        'author_ip'    => $ip,
        'created'      => date("Y-m-d H:i:s"),
        'content'      => $data['message'],
        'reply'        => $data['reply'],
    ]);

    if ($inserted === false) {
        bad_request("could_not_insert");
    }

    $comment = comment_for_FE(
        $db->queryFirstRow(
            "SELECT comment_id, reply, author_name, content, created, author_email, author_user FROM comments WHERE comment_id = %i LIMIT 1",
            $db->insertId()
        )
    );

    die(
    json_encode([
        'comment' => $comment,
    ])
    );
}

function ajax_register()
{
    $db            = App::getDb();
    $required_vars = [
        'userName'             => [
            'filter'  => FILTER_VALIDATE_REGEXP,
            'options' => [
                'regexp' => '/^[a-zA-Z0-9]([._-](?![._-])|[a-zA-Z0-9]){1,28}[a-zA-Z0-9]$/',
            ],
        ],
        'firstName'            => FILTER_SANITIZE_SPECIAL_CHARS,
        'lastName'             => FILTER_SANITIZE_SPECIAL_CHARS,
        'email'                => FILTER_VALIDATE_EMAIL,
        'password'             => FILTER_UNSAFE_RAW,
        'g-recaptcha-response' => FILTER_UNSAFE_RAW,
    ];

    $data = filter_input_array(INPUT_POST, $required_vars);
    $ip   = $_SERVER['REMOTE_ADDR'];

    foreach ($data as $name => $field) {
        if ($field === false || $field === null) {
            bad_request($name === 'email' ? 'email_validation_failure' : 'validation_failure');
        }
    }

    $data['userName']  = trim($data['userName']);
    $data['firstName'] = trim($data['firstName']);
    $data['lastName']  = trim($data['lastName']);
    $firstName_len     = strlen($data['firstName']);
    $lastName_len      = strlen($data['lastName']);

    if ($firstName_len < 2 || $firstName_len > 60 || $lastName_len < 2 || $lastName_len > 60) {
        bad_request('validation_failure');
    }

    if ( ! checkdnsrr(explode('@', $data['email'])[1] . '.', 'MX')) {
        bad_request('email_validation_failure');
    }

    validate_captcha($data['g-recaptcha-response'], $ip);

    $user_name_exists = $db->queryFirstField("SELECT COUNT(1) FROM users WHERE user_name = %s", $data['userName']);

    if ($user_name_exists) {
        bad_request("user_name_already_exists");
    }

    $user_email_exists = $db->queryFirstField("SELECT COUNT(1) FROM users WHERE email = %s", $data['email']);

    if ($user_email_exists) {
        bad_request("user_email_already_exists");
    }

    $password_hash = password_hash($data['password'], PASSWORD_DEFAULT);

    $user = $db->insert('users', [
        'user_name'  => $data['userName'],
        'first_name' => ucfirst($data['firstName']),
        'last_name'  => ucfirst($data['lastName']),
        'email'      => $data['email'],
        'password'   => $password_hash,
        'created'    => date("Y-m-d H:i:s"),
        'ip'         => $ip,
    ]);

    LoginTools::login($user);

    die(1);
}

function ajax_login()
{
    $db            = App::getDb();
    $required_vars = [
        'userName' => FILTER_UNSAFE_RAW,
        'password' => FILTER_UNSAFE_RAW,
    ];

    $data = filter_input_array(INPUT_POST, $required_vars);

    foreach ($data as $name => $field) {
        if ($field === false || $field === null) {
            bad_request('validation_failure');
        }
    }

    $data['userName'] = trim($data['userName']);

    // fetch user password hash by username or email
    $user = $db->queryFirstRow(
        "SELECT * FROM users WHERE user_name = %s OR email = %s",
        $data['userName'],
        $data['userName']
    );

    if ( ! $user) {
        bad_request('not_valid'); // don't inform the user that the username doesn't exist
    }

    if ( ! password_verify($data['password'], $user['password'])) {
        bad_request('not_valid');
    }

    LoginTools::login($user);

    die(1);
}

function ajax_logout()
{
    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    LoginTools::logout();

    die(1);
}

function ajax_upload_avatar()
{
    $db = App::getDb();

    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    $user = LoginTools::getUser();

    if ( ! isset($_FILES['avatar']) || $_FILES['avatar']['error'] !== 0) {
        if ($_FILES['avatar']['error'] === 2) {
            bad_request('avatar_too_big');
        }

        bad_request('upload_error');
    }

    $avatar          = $_FILES['avatar'];
    $avatar_size     = $avatar['size'];
    $avatar_type     = $avatar['type'];
    $avatar_tmp_name = $avatar['tmp_name'];

    if ($avatar_size > 1048576) {
        bad_request('avatar_too_big');
    }

    if ($avatar_type !== 'image/jpeg' && $avatar_type !== 'image/png') {
        bad_request('avatar_not_jpeg_or_png');
    }

    $avatar_url  = '/assets/img/uploads/avatars/' . $user->user_name . '.jpg';
    $avatar_path = __DIR__ . '/..' . $avatar_url;

    if (file_exists($avatar_path)) {
        unlink($avatar_path);
    }

    $image  = imagecreatefromstring(file_get_contents($avatar_tmp_name));
    $width  = imagesx($image);
    $height = imagesy($image);
    $size   = min($width, $height);
    $x      = ($width - $size) / 2;
    $y      = ($height - $size) / 2;
    $thumb  = imagecreatetruecolor($size, $size);
    imagecopyresampled($thumb, $image, 0, 0, $x, $y, $size, $size, $size, $size);
    imagejpeg($thumb, $avatar_path, 60);

    $db->update('users', [
        'avatar' => $avatar_url,
    ], 'user_name=%s', $user->user_name);
    die(1);
}

function ajax_remove_avatar()
{
    $db = App::getDb();

    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    $user = LoginTools::getUser();

    $db->update('users', [
        'avatar' => null,
    ], 'user_name=%s', $user->user_name);

    die(1);
}

function ajax_update_profile_details()
{
    $db = App::getDb();

    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    $user          = LoginTools::getUser();
    $required_vars = [
        'firstName'       => FILTER_SANITIZE_SPECIAL_CHARS,
        'lastName'        => FILTER_SANITIZE_SPECIAL_CHARS,
        'email'           => FILTER_VALIDATE_EMAIL,
        'password'        => FILTER_UNSAFE_RAW,
        'currentPassword' => FILTER_UNSAFE_RAW,
    ];

    $updateData = [];
    $data       = filter_input_array(INPUT_POST, $required_vars);

    if ($data['email'] === false) {
        bad_request('email_validation_failure');
    }

    if ($data['email'] !== null) {
        if ( ! checkdnsrr(explode('@', $data['email'])[1] . '.', 'MX')) {
            bad_request('email_validation_failure');
        }

        $user_email_exists = $db->queryFirstField("SELECT COUNT(1) FROM users WHERE email = %s", $data['email']);

        if ($user_email_exists) {
            bad_request("email_already_exists");
        }
    }

    if ($data['firstName']) {
        $updateData['first_name'] = ucfirst($data['firstName']);
    }

    if ($data['lastName']) {
        $updateData['last_name'] = ucfirst($data['lastName']);
    }

    if ($data['email']) {
        $updateData['email'] = $data['email'];
    }

    if ($data['password']) {
        if ( ! password_verify($data['currentPassword'], $user->password)) {
            bad_request('invalid_password');
        }

        $updateData['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
    }

    if (count($updateData) > 0) {
        $db->update('users', $updateData, 'user_name=%s', $user->user_name);
    }
    die(1);
}

function ajax_edit_comment()
{
    $db = App::getDb();

    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    $user          = LoginTools::getUser();
    $required_vars = [
        'comment_id' => FILTER_VALIDATE_INT,
        'message'    => FILTER_SANITIZE_SPECIAL_CHARS,
    ];

    $data = filter_input_array(INPUT_POST, $required_vars);

    foreach ($data as $field) {
        if ($field === false || $field === null) {
            bad_request("validation_failure");
        }
    }

    $commentUser = $db->queryFirstField("SELECT author_user FROM comments WHERE comment_id = %d", $data['comment_id']);

    if ($commentUser !== $user->user_name) {
        bad_request('not_authorised');
    }

    $data['message'] = trim($data['message']);

    $db->update('comments', [
        'content' => $data['message'],
    ], 'comment_id=%s', $data['comment_id']);

    $updatedComment = $db->queryFirstRow(
        "SELECT comment_id, reply, author_name, content, created, author_email, author_user FROM comments WHERE comment_id = %d",
        $data['comment_id']
    );

    die(json_encode(comment_for_FE($updatedComment)));
}

function ajax_delete_comment()
{
    $db = App::getDb();

    if ( ! LoginTools::isLoggedIn()) {
        bad_request('not_logged_in');
    }

    $user          = LoginTools::getUser();
    $required_vars = [
        'comment_id' => FILTER_VALIDATE_INT,
    ];

    $data = filter_input_array(INPUT_POST, $required_vars);
    foreach ($data as $field) {
        if ($field === false || $field === null) {
            bad_request("validation_failure");
        }
    }

    $commentUser = $db->queryFirstField("SELECT author_user FROM comments WHERE comment_id = %d", $data['comment_id']);

    if ($commentUser !== $user->user_name) {
        bad_request('not_authorised');
    }

    $db->delete('comments', 'comment_id=%s', $data['comment_id']);

    echo($data['comment_id']);
    die();
}