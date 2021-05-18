<?php
global $db;

$slug = strtolower($_GET['slug'] ?? "");
$post = null;

if (!empty($slug))
    $post = $db->queryFirstRow("SELECT * FROM posts WHERE LOWER(slug) = %s LIMIT 1", $slug);

if (empty($post))
    return throw_err_404();

$comments_count = $db->queryFirstField("SELECT COUNT(*) FROM comments WHERE post_id = %i", $post['id']);
$tags = $db->query("
                SELECT name, slug
                FROM tags
                    INNER JOIN tags_relationships tr on tags.id = tr.tag_id
                WHERE tr.post_id = %i
                GROUP BY id ORDER BY name
            ", $post['id']);
$karma_sql = $db->queryFullColumns('
    SELECT value AS v, COUNT(value) AS c
    FROM karma
    WHERE type = "post" AND obj_id = %i
    GROUP BY value
', $post['id']);

$karma = [
    'positive' => 0,
    'negative' => 0
];

foreach ($karma_sql as $row)
    $karma[$row['karma.v'] == '1' ? 'positive' : 'negative'] = $row['c'];

$karma_sum = $karma['positive'] - $karma['negative'];

$pure_url = strtok((isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]", '?');

$GLOBALS['page_data'] = [
    'title' => $post['title'] . ' - TechNews',
    'header' => [
        'hide_header' => true,
    ],
    'no_container' => true,
    'og_meta' => [
        'type' => 'article',
        'url' => $pure_url,
        'title' => $post['title'],
        'description' => get_excerpt($post['content'], 60),
        'image' => $pure_url . $post['image']
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
                        <i class="far fa-calendar-alt"></i> <?= nice_date($post['date']) ?>
                    </div>
                    <div>
                        <i class="far fa-comment"></i> <?= $comments_count ?> <?= get_comments_noun($comments_count) ?>
                    </div>
                    <div>
                        <i class="far fa-thumbs-up"></i>
                        <span class="text-<?= $karma_sum < 0 ? "danger" : "success" ?> font-weight-bold"><?= $karma_sum ?></span>
                        hodnocení
                    </div>
                    <div>
                        <i class="fas fa-mug-hot"></i>
                        Četba na <span class="reading-time"></span>
                    </div>
                </div>
            </div>
            <div class="col-auto share-social-buttons">
                <i class="fab fa-facebook-square" title="Sdílet na Facebook"></i>
                <i class="fab fa-twitter-square" title="Sdílet na Twitter"></i>
                <i class="fab fa-linkedin" title="Sdílet na LinkedIn"></i>
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

    <div data-parallax="scroll" data-image-src="<?= $post['image'] ?>"
         id="parallax-image"></div>

    <div id="article-content" class="container pt-5 pb-5">
        <div class="row">
            <div class="col">
                <div class="content pb-2">
                    <?= $post['content'] ?>
                </div>
                <hr>
                <div class="row">
                    <?php
                    $votes_cookie = json_decode($_COOKIE['votes'] ?? '[]');
                    $vote_row = $db->queryFirstRow("
                        SELECT *
                        FROM karma
                        WHERE obj_id = %i AND ip = %s AND type = 'post'
                    ", $post['id'], $_SERVER['REMOTE_ADDR']);
                    $voted = !empty($vote_row) || in_array($post['id'], $votes_cookie);
                    ?>
                    <div class="col thumb-rating <?= $voted ? "voted" : "" ?>" data-id="<?= $post['id'] ?>">
                        Názor na článek:
                        <span class="like text-success">
                            <div class="icons <?= $vote_row['value'] == 1 ? "vote" : "" ?>">
                                <i class="far fa-thumbs-up"></i><i class="fas fa-thumbs-up"></i>
                            </div>
                            (<span class="votes"><?= $karma['positive'] ?></span>)
                        </span>
                        <span class="dislike text-danger">
                            <div class="icons <?= $vote_row['value'] == -1 ? "vote" : "" ?>">
                                <i class="far fa-thumbs-down"></i><i class="fas fa-thumbs-down"></i>
                            </div>
                            (<span class="votes"><?= $karma['negative'] ?></span>)
                        </span>
                    </div>
                    <div class="col-auto share-social-buttons">
                        <i class="fab fa-facebook-square" title="Sdílet na Facebook"></i>
                        <i class="fab fa-twitter-square" title="Sdílet na Twitter"></i>
                        <i class="fab fa-linkedin" title="Sdílet na LinkedIn"></i>
                    </div>
                </div>
            </div>
            <?php
            get_the_sidebar();
            ?>
        </div>
    </div>
    <div id="article-comment">
        <div class="h3">Komentáře</div>
    </div>
</article>
