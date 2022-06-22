<?php

function get_the_sidebar()
{
    $db = App::getDb();

    ?>
    <div class="sidebar col-lg-4 mt-5 mt-lg-0">
        <div class="tags-list">
            <div class="h5">Populární štítky</div>
            <?php
            $tags = $db->query(
                "SELECT t.name, t.slug 
                FROM tags AS t
                    LEFT JOIN tags_relationships AS tr ON t.id = tr.tag_id
                GROUP BY t.id
                ORDER BY COUNT(tr.post_id) DESC LIMIT 5"
            );
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
                $popular_posts = $db->query(
                    'SELECT p.*, SUM(k.value) AS karma 
                    FROM karma AS k 
                        INNER JOIN posts AS p ON p.id = k.obj_id 
                    WHERE type = "post"
                    GROUP BY obj_id ORDER BY karma DESC LIMIT 3'
                );

                foreach ($popular_posts as $post):
                    ?>
                    <div class="row">
                        <div class="image col">
                            <a href="/Clanek/<?= ucfirst($post['slug']) ?>">
                                <img src="<?= PostTools::getImageSize($post['image'], 'h75') ?>"
                                     alt="<?= $post['title'] ?>">
                            </a>
                        </div>
                        <div class="content col">
                            <a href="/Clanek/<?= ucfirst($post['slug']) ?>" class="text-decoration-none">
                                <h3><?= PostTools::getExcerpt($post['title'], 45) ?></h3>
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
            <endora/>
        </div>
    </div>
    <?php
}

function get_profile_sidebar($active_index = 0)
{
    $active                = array_fill(0, 2, '');
    $active[$active_index] = 'active';
    ?>
    <div class="sidebar col-lg-3 mt-5 mt-lg-0">
        <ul class="nav nav-pills flex-column mb-auto">
            <li class="nav-item">
                <a href="/Profil" class="nav-link <?= $active[0] ?>">
                    <i class="fas fa-user"></i>Můj profil
                </a>
            </li>
            <li class="nav-item">
                <a href="/Profil/nastaveni" class="nav-link <?= $active[1] ?>">
                    <i class="fas fa-tools"></i>Nastavení
                </a>
            </li>
            <li class="nav-item">
                <a href="#" class="nav-link logout-link text-danger">
                    <i class="fas fa-sign-out-alt"></i>Odhlásit se
                </a>
            </li>
        </ul>
        <div id="advertisement" class="mt-5">
            <div class="h5">Reklama</div>
            <endora/>
        </div>
    </div>
    <?php
}
