<?php

if ( ! LoginTools::isLoggedIn()) {
    header('Location: /');
    die();
}

$db   = App::getDb();
$user = LoginTools::getUser();

$GLOBALS['page_data'] = [
    'title'  => 'Můj profil - TechNews',
    'header' => [
        'active_index' => 4,
        'image'        => '/assets/img/graphics/header.jpg',
        'title'        => sprintf('<img src="%s" width="100" alt="profile avatar" class="profile-avatar"/>Nastvení',
            UserTools::getAvatar($user))
    ]
];

?>
<div class="row">
    <?php
    get_profile_sidebar(1);
    ?>
    <div class="col">
        <h1>Nastavení</h1>

    </div>
</div>
