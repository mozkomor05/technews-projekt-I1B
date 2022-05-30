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
        'title'        => sprintf('<img src="%s" width="100" alt="profile avatar" class="profile-avatar"/>Váš profil, %s',
            UserTools::getAvatar($user), UserTools::vokativ($user)),
    ]
];

$comments = $db->query(
    'SELECT p.title, p.slug, c.content, c.created 
        FROM comments c
            INNER JOIN posts p ON p.id = c.post_id
        WHERE c.author_user = %i
        ORDER BY c.created DESC',
    $user->user_name
);

?>
<div class="row">
    <?php
    get_profile_sidebar();
    ?>
    <div class="col">
        <div>
            <h1>Základní informace</h1>
            <div class="my-4 row row-cols-auto">
                <div class="col">
                    <div><strong>Uživatelské jméno</strong>:</div>
                    <div><strong>Vystupujete jako</strong>:</div>
                    <div><strong>E-mail</strong>: <i>(soukromý)</i></div>
                    <div><strong>Datum registrace</strong>:</div>
                    <div><strong>Počet komentářů</strong>:</div>
                </div>
                <div class="col">
                    <div><?= $user->user_name ?></div>
                    <div><?= UserTools::getNiceName($user) ?></div>
                    <div><?= $user->email ?></div>
                    <div><?= PostTools::getNiceDate($user->created) ?></div>
                    <div><?= count($comments) ?></div>
                </div>
            </div>
        </div>
        <div>
            <h1 class="mt-5">Vaše komentáře</h1>
            <table class="table my-4">
                <thead>
                <tr>
                    <th scope="col" style="width: 20%">Datum</th>
                    <th scope="col" style="width: 20%">Příspěvek</th>
                    <th scope="col">Obsah</th>
                </tr>
                </thead>
                <tbody>
                <?php
                foreach ($comments as $comment) {
                    ?>
                    <tr>
                        <th scope="row"><?= PostTools::getNiceDate($comment['created']) ?></
                        >
                        <td><a href="/Clanek/<?= $comment['slug'] ?>"><?= $comment['title'] ?></a></td>
                        <td><?= PostTools::getExcerpt($comment['content'], 300) ?></td>
                    </tr>
                    <?php
                }
                ?>
                </tbody>
            </table>
        </div>
    </div>
</div>
