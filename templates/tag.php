<?php
global $db;

$slug = strtolower($_GET['slug'] ?? "");
$tag = null;

if (!empty($slug))
    $tag = $db->queryFirstRow("SELECT * FROM tags WHERE LOWER(slug) = %s LIMIT 1", $slug);

if (empty($tag))
    return throw_err_404();

$tag_image = get_image_size($tag['image'], 'largest');

$GLOBALS['page_data'] = [
    'title' => $tag['name'] . ', štítek - TechNews',
    'header' => [
        'active_index' => 1,
        'image' => $tag_image,
        'title' => 'Štítek - ' . $tag['name']
    ],
    'og_meta' => [
        'type' => 'website',
        'url' => get_current_url(),
        'description' => get_excerpt($tag['description'], 300),
        'image' => get_site_url() . $tag_image
    ]
];

?>
<div>
    <h1><?= $tag['name'] ?></h1>
    <p><?= $tag['description'] ?></p>
    <hr>
    <div id="posts-archive">
        <?php
        $posts = $db->query('
            SELECT p.*, COUNT(c.comment_id) AS comments_count, k.karma AS karma
            FROM posts AS p
                LEFT JOIN comments AS c ON p.id = c.post_id
                LEFT JOIN (SELECT obj_id, SUM(value) AS karma FROM karma WHERE type = "post" GROUP BY obj_id) AS k
                       ON k.obj_id = p.id
                LEFT JOIN tags_relationships tr ON p.id = tr.post_id
            WHERE tr.tag_id = %i
            GROUP BY p.id
        ', $tag['id']);
        if (count($posts))
            posts_archive_loop($posts, 250);
        else {
            ?>
            <div class="text-center">
                Tento štítek zatím neobsahuje žádné příspěvky.
            </div>
            <?php
        }
        ?>
    </div>
    <endora>
</div>