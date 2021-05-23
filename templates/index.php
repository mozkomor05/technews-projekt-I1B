<?php
global $db;

$GLOBALS['page_data'] = [
    'main_page' => true
];
?>
<div class="row" id="newest-anchor">
    <div class="col-lg-8">
        <h1>Nejnovější</h1>
        <p>Seznam nejnovějších článků.</p>
        <hr>
        <div id="posts-archive">
            <?php
            $posts = $db->query('
                SELECT p.*, COUNT(c.comment_id) AS comments_count, k.karma AS karma
                FROM posts AS p
                         LEFT JOIN comments AS c ON p.id = c.post_id
                         LEFT JOIN (SELECT obj_id, SUM(value) AS karma FROM karma WHERE type = "post" GROUP BY obj_id) AS k
                                   ON k.obj_id = p.id
                GROUP BY p.id
                ORDER BY p.date DESC
            ');

            foreach ($posts as $post):
                $tags = $db->query('
                    SELECT t.name, t.slug
                    FROM tags AS t
                        INNER JOIN tags_relationships tr on t.id = tr.tag_id
                    WHERE tr.post_id = %i
                ', $post['id']);

                $tags_html = implode(', ', array_map(function ($tag) {
                    return "<a href=\"/Stitek/" . ucfirst($tag['slug']) . "\">" . $tag['name'] . "</a>";
                }, $tags)) ?: "Žádné";

                if ($post['karma'] === null)
                    $post['karma'] = 0;

                ?>
                <div class="row">
                    <div class="content col-8">
                        <div class="tags">
                            <i class="fas fa-tags"></i> <?= $tags_html ?>
                        </div>
                        <a href="/Clanek/<?= ucfirst($post['slug']) ?>" class="text-reset text-decoration-none">
                            <h3><?= $post['title'] ?></h3>
                            <p>
                                <?= get_excerpt($post['content'], 20) ?>
                            </p>
                        </a>
                        <div class="info-icons">
                            <div>
                                <i class="far fa-calendar-alt"></i> <?= nice_date($post['date']) ?>
                            </div>
                            <div>
                                <i class="far fa-comment"></i> <?= $post['comments_count'] ?> <?= get_comments_noun($post['comments_count']) ?>
                            </div>
                            <div>
                                <i class="far fa-thumbs-up"></i>
                                <span class="text-<?= $post['karma'] < 0 ? "danger" : "success" ?> fw-bold"><?= $post['karma'] ?></span>
                                hodnocení
                            </div>
                        </div>
                    </div>
                    <div class="image col-4">
                        <a href="/Clanek/<?= ucfirst($post['slug']) ?>">
                            <img src="<?= $post['image'] ?>" alt="<?= $post['title'] ?>" class="w-100">
                        </a>
                    </div>
                </div>
            <?php
            endforeach;
            ?>
        </div>
    </div>
    <?php
    get_the_sidebar();
    ?>
</div>