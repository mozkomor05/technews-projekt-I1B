<?php

function get_the_sidebar()
{
    global $db;

    ?>
    <div class="sidebar col-lg-4 mt-lg-5">
        <div class="tags-list">
            <div class="h5">Populární štítky</div>
            <?php
            $tags = $db->query("
                SELECT t.name, t.slug
                FROM tags AS t
                    LEFT JOIN tags_relationships AS tr ON t.id = tr.tag_id
                GROUP BY t.id
                ORDER BY COUNT(tr.post_id) DESC LIMIT 5
            ");
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
        </div>
        <div>
            <div class="h5">Oblíbené</div>
            <div id="popular-posts">
                <?php
                $popular_posts = $db->query('
                    SELECT p.*, SUM(k.value) AS karma 
                    FROM karma AS k 
                        INNER JOIN posts AS p ON p.id = k.obj_id 
                    WHERE type = "post"
                    GROUP BY obj_id ORDER BY karma DESC LIMIT 3
            ');

                foreach ($popular_posts as $post):
                    ?>
                    <div class="row">
                        <div class="image col">
                            <a href="/Clanek/<?= ucfirst($post['slug']) ?>">
                                <img src="<?= get_image_size($post['image'], 'h75') ?>" alt="<?= $post['title'] ?>">
                            </a>
                        </div>
                        <div class="content col">
                            <a href="/Clanek/<?= ucfirst($post['slug']) ?>" class="text-decoration-none">
                                <h3><?= get_excerpt($post['title'], 45) ?></h3>
                                <div class="read">číst ></div>
                            </a>
                        </div>
                    </div>
                <?php
                endforeach;
                ?>
            </div>
        </div>
        <div id="advertisement">
            <div class="h5">Reklama</div>
            <endora>
        </div>
    </div>
    <?php
}