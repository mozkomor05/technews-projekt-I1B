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
    'negative' => 0,
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
        'image'       => PostTools::getSiteUrl() . $post_image,
    ],
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
                        <i class="far fa-comment"></i> <?= $comments_count ?> <?= PostTools::getCommentNoun(
                            $comments_count
                        ) ?>
                    </div>
                    <div>
                        <i class="far fa-thumbs-up"></i>
                        <span class="text-<?= $karma_sum < 0 ? "danger" : "success" ?> fw-bold"><?= $karma_sum ?></span>
                        hodnocen??
                    </div>
                    <div>
                        <i class="fas fa-mug-hot"></i>
                        ??etba na <span class="reading-time"></span>
                    </div>
                </div>
            </div>
            <div class="col-auto share-social-buttons">
                <i class="fab fa-facebook-square" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sd??let na Facebook"></i>
                <i class="fab fa-twitter-square" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sd??let na Twitter"></i>
                <i class="fab fa-linkedin" data-bs-toggle="tooltip" data-bs-placement="top"
                   title="Sd??let na LinkedIn"></i>
            </div>
        </div>
        <hr>
        <div class="post-tags">
            <div class="tags-title">??t??tky:</div>
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
                    ????dn??
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
                        N??zor na ??l??nek:
                        <span class="like text-success">
                            <div data-toggle="popover" data-bs-toggle="tooltip" data-bs-placement="top" title="L??b??"
                                 class="icons <?= $vote_row['value'] === 1 ? 'vote' : '' ?>">
                                <i class="far fa-thumbs-up"></i><i class="fas fa-thumbs-up"></i>
                            </div>
                            (<span class="votes"><?= $karma['positive'] ?></span>)
                        </span>
                        <span class="dislike text-danger">
                            <div data-bs-toggle="tooltip" data-bs-placement="top" title="Nel??b??"
                                 class="icons <?= $vote_row['value'] === -1 ? "vote" : "" ?>">
                                <i class="far fa-thumbs-down"></i><i class="fas fa-thumbs-down"></i>
                            </div>
                            (<span class="votes"><?= $karma['negative'] ?></span>)
                        </span>
                    </div>
                    <div class="col-auto share-social-buttons">
                        <i class="fab fa-facebook-square" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sd??let na Facebook"></i>
                        <i class="fab fa-twitter-square" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sd??let na Twitter"></i>
                        <i class="fab fa-linkedin" data-bs-toggle="tooltip" data-bs-placement="top"
                           title="Sd??let na LinkedIn"></i>
                    </div>
                </div>
                <div id="article-comments">
                    <div class="h3">Koment????e</div>
                    <div>
                        P??idejte vlastn?? koment????, formul???? je na <a class="js-anchor" href="#comment-form">konci
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
                                                <i class="fas fa-reply"></i> Odpov???? na <a href="#"
                                                                                           class="reply-to-comment js-anchor"></a>
                                            </li>
                                            <li class="list-inline-item date"></li>
                                            <li class="list-inline-item">
                                                <a href="#" class="comments-section-reply-btn">Odpov??d??t</a>
                                            </li>
                                            <li class="list-inline-item" style="display:none">
                                                <a href="#" class="comments-section-edit-btn text-secondary hide"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#editModal">Editovat</a>
                                            </li>
                                            <li class="list-inline-item" style="display:none">
                                                <a href="#" class="comments-section-delete-btn text-danger"
                                                   data-bs-toggle="modal"
                                                   data-bs-target="#deleteModal">Smazat</a>
                                            </li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                            <div class="mt-3 content pb-3"></div>
                        </div>
                    </div>
                    <div class="show-more pb-5 text-center">
                        <button class="btn btn-primary" style="display:none" type="submit">Zobrazit v??ce
                        </button>
                    </div>
                    <form id="comment-form" class="needs-validation" novalidate>
                        <div class="fw-bold mb-3 fs-3">Pod??lte se o post??eh...</div>
                        <div id="alert-container">
                            <div class="error-alert alert alert-warning alert-dismissible fade show" role="alert"
                                 style="display: none">
                                <div class="content"></div>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"
                                        aria-label="Close"></button>
                            </div>
                            <div class="reply-alert alert alert-primary alert-dismissible fade show" role="alert"
                                 style="display: none">
                                Tento koment???? se za??le jako <strong>odpov????</strong> na <a href="#"
                                                                                            class="reply-to js-anchor"></a>.
                                <em>(kliknut??m na k??????ek deaktivujete)</em>
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
                                    sd??lejte sv??j n??zor.
                                </div>
                            </div>
                        <?php
                        else:
                            ?>
                            <div class="form-row">
                                <div class="form-floating col mb-3">
                                    <input type="text" class="form-control " id="comment_form-name" placeholder="Jm??no"
                                           minlength="3" max="40" name="name" required
                                           aria-describedby="help_block-name">
                                    <label for="comment_form-name">Jm??no</label>
                                    <div class="invalid-feedback">
                                        Jm??no (d??l???? t???? znak??) je povinn??.
                                    </div>
                                    <div id="help_block-name" class="form-text">
                                        Ide??ln?? unik??tn?? p??ezd??vka, pod kterou budete vystupovat.
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
                                        Pros??m zadejte validn?? e-mail.
                                    </div>
                                    <div id="help_block-email" class="form-text">
                                        Nebude zve??ejn??n.
                                    </div>
                                </div>
                            </div>
                        <?php
                        endif;
                        ?>
                        <div class="form-row">
                            <div class="form-floating col mb-3">
                                <textarea class="form-control" id="comment_form-message" maxlength="2000"
                                          name="message" placeholder="Va??e zpr??va..." required></textarea>
                                <label for="comment_form-message">Zpr??va</label>
                                <div class="invalid-feedback">
                                    Va??e zpr??va nem????e b??t pr??zdn??.
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
                                    Prove??te ov????en??.
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
<div class="modal fade" id="editModal" data-bs-backdrop="static" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="editModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="editModalLabel">Editace koment????e</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zav????t"></button>
            </div>
            <form id="editCommentForm">
                <div class="modal-body">
                    <div class="mb-3">
                        <input type="hidden" name="comment_id">
                        <label for="edit-message-text" class="col-form-label">V???? koment????:</label>
                        <textarea class="form-control" id="edit-message-text" name="message"></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zav????t</button>
                    <button type="submit" class="btn btn-primary">Editovat</button>
                </div>
            </form>
        </div>
    </div>
</div>
<div class="modal fade" id="deleteModal" data-bs-keyboard="false" tabindex="-1"
     aria-labelledby="deleteModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="deleteModalLabel">Smazat koment????</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zav????t"></button>
            </div>
            <form id="deleteCommentForm">
                <input type="hidden" name="comment_id">
                <div class="modal-body">
                    Opravdu si p??ejete nen??vratn?? smazat koment?????
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zav????t</button>
                    <button type="submit" class="btn btn-danger">Smazat</button>
                </div>
            </form>
        </div>
    </div>
</div>