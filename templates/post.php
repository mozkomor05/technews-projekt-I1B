<?php
global $db;

$slug = strtolower($_GET['slug'] ?? "");
$post = null;

if (!empty($slug))
    $post = $db->queryFirstRow("SELECT * FROM posts WHERE LOWER(slug) = %s", $slug);

if (empty($post))
    return throw_err_404();

$GLOBALS['page_data'] = [
    'title' => $post['title'] . ' - TechNews',
    'header' => [
        'hide_header' => true,
    ],
    'no_container' => true
];
?>

<article>
    <div id="article-header" class="mb-5 container">
        <h1><?= $post['title'] ?></h1>

    </div>

    <div data-parallax="scroll" data-image-src="<?= $post['image'] ?>" data-position-y="-100%"
         id="parallax-image"></div>

    <div id="article-content" class="container pt-5">
        <div class="row">
            <div class="col">
                <?=$post['content']?>
            </div>
            <?php
            get_the_sidebar();
            ?>
        </div>
    </div>
</article>
