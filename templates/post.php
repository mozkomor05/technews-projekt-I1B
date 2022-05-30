<?php

$db   = App::getDb();
$slug = strtolower($_GET['slug'] ?? "");
$post = null;

if ( ! empty($slug)) {
    $post = $db->queryFirstRow("SELECT * FROM posts WHERE LOWER(slug) = %s LIMIT 1", $slug);
}

if (empty($post)) {
    return throw_err_404();
}

$comments_count = $db->queryFirstField("SELECT COUNT(*) FROM comments WHERE post_id = %i", $post['id']);
$tags           = $db->query(
    "SELECT name, slug
    FROM tags
        INNER JOIN tags_relationships tr on tags.id = tr.tag_id
    WHERE tr.post_id = %i
    GROUP BY id ORDER BY name",
    $post['id']
);
$karma_sql      = $db->queryFullColumns(
    'SELECT value AS v, COUNT(value) AS c
    FROM karma
    WHERE type = "post" AND obj_id = %i
    GROUP BY value',
    $post['id']
);

$karma = [
    'positive' => 0,
    'negative' => 0
];

foreach ($karma_sql as $row) {
    $karma[$row['karma.v'] == '1' ? 'positive' : 'negative'] = $row['c'];
}

$karma_sum = $karma['positive'] - $karma['negative'];

$post_image = PostTools::getImageSize($post['image'], 'largest');

$GLOBALS['page_data'] = [
    'title'        => $post['title'] . ' - TechNews',
    'header'       => [
        'hide_header' => true,
    ],
    'no_container' => true,
    'og_meta'      => [
        'type'        => 'article',
        'url'         => PostTools::getCurrentUrl(),
        'title'       => $post['title'],
        'description' => PostTools::getExcerpt($post['content'], 300),
        'image'       => PostTools::getSiteUrl() . $post_image
    ]
];
?>

<article>
    <div id="article-header" class="mb-5 container">
        <h1 class="mb-5"><?= $post['title'] ?></h1>
        <div class="row">
            <div class="col">
                <div class="info-icons">
                    <div>
                        <i class="far fa-calendar-alt"></i> <?= PostTools::getNiceDate($post['date']) ?>
                    </div>
                    <div>
                        <i class="far fa-comment"></i> <?= $comments_count ?> <?= PostTools::getCommentNoun($comments_count) ?>
                    </div>
                    <div>
                        <i class="far fa-thumbs-up"></i>
                        <span class="text-<?= $karma_sum < 0 ? "danger" : "success" ?> fw-bold"><?= $karma_sum ?></span>
                        hodnocení
                    </div>
                    <div>
                        <i class="fas fa-mug-hot"></i>
                        Četba na <span class="reading-time"></span>
                    </div>
                </div>
            </div>
            <div class="col-auto share-social-buttons">
                <i class="fab fa-facebook-square" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sdílet na Facebook"></i>
                <i class="fab fa-twitter-square" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sdílet na Twitter"></i>
                <i class="fab fa-linkedin" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sdílet na LinkedIn"></i>
            </div>
        </div>
        <hr>
        <div class="post-tags">
            <div class="tags-title">Štítky:</div>
            <div class="tags-list">
                <?php
                if (count($tags)):
                    ?>
                    <ul>
                        <?php
                        foreach ($tags as $tag):
                            ?>
                            <li><a href="/Stitek/<?= ucfirst($tag['slug']) ?>"><?= $tag['name'] ?></a></li>
                        <?php
                        endforeach;
                        ?>
                    </ul>
                <?php
                else:
                    ?>
                    Žádné
                <?php
                endif;
                ?>
            </div>
        </div>
    </div>

    <div data-parallax="scroll" data-image-src="<?= $post_image ?>"
         id="parallax-image"></div>

    <div id="article-content" class="container pt-5 pb-4" data-id="<?= $post['id'] ?>">
        <div class="row">
            <div class="col">
                <div class="content pb-2">
                    <?= $post['content'] ?>
                </div>
                <hr>
                <div class="row">
                    <?php
                    $votes_cookie = json_decode($_COOKIE['votes'] ?? '[]');
                    $vote_row     = $db->queryFirstRow(
                        "SELECT *
                        FROM karma
                        WHERE obj_id = %i AND ip = %s AND type = 'post'",
                        $post['id'],
                        $_SERVER['REMOTE_ADDR']
                    );
                    $voted        = true;

                    if ( ! $vote_row) {
                        $vote_row = [
                            'id'    => 0,
                            'value' => 0,
                        ];

                        $voted = in_array($post['id'], $votes_cookie);
                    }

                    $vote_row['value'] = intval($vote_row['value']);
                    ?>
                    <div class="col pb-5 thumb-rating <?= $voted ? "voted" : "" ?>">
                        Názor na článek:
                        <span class="like text-success">
                            <div data-toggle="popover" data-bs-toggle="tooltip" data-bs-placement="top" title="Líbí"
                                 class="icons <?= $vote_row['value'] === 1 ? 'vote' : '' ?>">
                                <i class="far fa-thumbs-up"></i><i class="fas fa-thumbs-up"></i>
                            </div>
                            (<span class="votes"><?= $karma['positive'] ?></span>)
                        </span>
                        <span class="dislike text-danger">
                            <div data-bs-toggle="tooltip" data-bs-placement="top" title="Nelíbí"
                                 class="icons <?= $vote_row['value'] === -1 ? "vote" : "" ?>">
                                <i class="far fa-thumbs-down"></i><i class="fas fa-thumbs-down"></i>
                            </div>
                            (<span class="votes"><?= $karma['negative'] ?></span>)
                        </span>
                    </div>
                    <div class="col-auto share-social-buttons">
                        <i class="fab fa-facebook-square" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sdílet na Facebook"></i>
                        <i class="fab fa-twitter-square" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sdílet na Twitter"></i>
                        <i class="fab fa-linkedin" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sdílet na LinkedIn"></i>
                    </div>
                </div>
                <div id="article-comments">
                    <div class="h3">Komentáře</div>
                    <div>
                        Přidejte vlastní komentář, formulář je na <a class="js-anchor" href="#comment-form">konci
                            sekce</a>.
                    </div>
                    <div class="comments-section mt-3">
                        <div class="comment pb-3 pt-4" style="display:none">
                            <div class="row align-items-center">
                                <div class="col-auto">
                                    <img src="" class="image" alt="profile picture" width="64">
                                </div>
                                <div class="col">
                                    <div class="fw-bold fs-5 name"></div>
                                    <div class="info">
                                        <ul class="list-inline mb-0">
                                            <li class="list-inline-item reply-to">
                                                <i class="fas fa-reply"></i> Odpověď na <a href="#"
                                                                                           class="reply-to-comment js-anchor"></a>
                                            </li>
                                            <li class="list-inline-item date"></li>
                                            <li class="list-inline-item">
                                                <a href="#" class="comments-section-reply-btn">Odpovědět</a>
                                            </li>
                                            <li class="list-inline-item">
                                                <a href="#" class="comments-section-edit-btn text-secondary hide"
                                                   style="display:none">Editovat</a>
                                            </li>
                                            <li class="list-inline-item">
                                                <a href="#" class="comments-section-delete-btn text-danger"
                                                   style="display:none">Smazat</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 content pb-3"></div>
                        </div>
                    </div>
                    <div class="show-more pb-5 text-center">
                        <button class="btn btn-outline-primary" style="display:none" type="submit">Zobrazit více
                        </button>
                    </div>
                    <form id="comment-form" class="needs-validation" novalidate>
                        <div class="fw-bold mb-3 fs-3">Podělte se o postřeh...</div>
                        <div id="alert-container">
                            <div class="error-alert alert alert-warning alert-dismissible fade show" role="alert"
                                 style="display: none">
                                <div class="content"></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                            </div>
                            <div class="reply-alert alert alert-primary alert-dismissible fade show" role="alert"
                                 style="display: none">
                                Tento komentář se zašle jako <strong>odpověď</strong> na <a href="#"
                                                                                            class="reply-to js-anchor"></a>.
                                <em>(kliknutím na křížek deaktivujete)</em>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                            </div>
                        </div>
                        <input type="hidden" id="reply-to" name="reply" value="-1">
                        <?php
                        if (LoginTools::isLoggedIn()):
                            $user = LoginTools::getUser();
                            ?>
                            <div class="alert alert-primary" role="alert">
                                <div class="content"><em><strong><?= UserTools::vokativ($user) ?></strong></em>,
                                    sdílejte svůj názor.
                                </div>
                            </div>
                        <?php
                        else:
                            ?>
                            <div class="form-row">
                                <div class="form-floating col mb-3">
                                    <input type="text" class="form-control " id="comment_form-name" placeholder="Jméno"
                                           minlength="3" max="40" name="name" required
                                           aria-describedby="help_block-name">
                                    <label for="comment_form-name">Jméno</label>
                                    <div class="invalid-feedback">
                                        Jméno (délší tří znaků) je povinné.
                                    </div>
                                    <div id="help_block-name" class="form-text">
                                        Ideálně unikátní přezdívka, pod kterou budete vystupovat.
                                    </div>
                                </div>
                            </div>
                            <div class="form-row">
                                <div class="form-floating col mb-3">
                                    <input type="email" class="form-control" id="comment_form-email"
                                           placeholder="E-mail"
                                           name="email" aria-describedby="help_block-email" required>
                                    <label for="comment_form-email">E-mail</label>
                                    <div class="invalid-feedback">
                                        Prosím zadejte validní e-mail.
                                    </div>
                                    <div id="help_block-email" class="form-text">
                                        Nebude zveřejněn.
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;
                        ?>
                        <div class="form-row">
                            <div class="form-floating col mb-3">
                                <textarea class="form-control" id="comment_form-message" maxlength="2000"
                                          name="message" placeholder="Vaše zpráva..." required></textarea>
                                <label for="comment_form-message">Zpráva</label>
                                <div class="invalid-feedback">
                                    Vaše zpráva nemůže být prázdná.
                                </div>
                            </div>
                        </div>
                        <?php
                        if ( ! LoginTools::isLoggedIn()):
                            ?>
                            <div class="form-row mb-3">
                                <div class="g-recaptcha"
                                     data-sitekey="<?= App::getConfig()->get(['recaptcha', 'site']) ?>"
                                     data-callback="grecaptchaValidated"></div>
                                <div class="invalid-feedback">
                                    Proveďte ověření.
                                </div>
                            </div>
                        <?php
                        endif;
                        ?>
                        <button class="btn btn-primary" type="submit">Odeslat</button>
                    </form>
                </div>
            </div>
            <?php
            get_the_sidebar();
            ?>
        </div>
    </div>
</article>
