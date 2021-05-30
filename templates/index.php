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
                LIMIT 15
            ');

            posts_archive_loop($posts);

            if (count($posts) >  15):
                ?>
                <div class="text-center"><a class="btn btn-primary" href="/Vypis-clanku">Všechny články</a></div>
            <?php
            endif;
            ?>
        </div>
    </div>
    <?php
    get_the_sidebar();
    ?>
</div>