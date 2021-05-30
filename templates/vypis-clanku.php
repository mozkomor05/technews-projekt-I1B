<?php
global $db;

$GLOBALS['page_data'] = [
    'title' => 'Výpis článků - TechNews',
    'header' => [
        'active_index' => 1,
        'image' => '/assets/img/graphics/header.jpg',
        'title' => 'Výpis článků a štítků'
    ],
    'scripts' => [
        '/assets/js/lightslider.min.js'
    ],
    'styles' => [
        '/assets/css/lightslider.min.css'
    ]
];

$random_post_slug = $db->queryFirstField("SELECT slug FROM posts ORDER BY RAND() LIMIT 1");

$tags = $db->query("
    SELECT t.*, COUNT(r.post_id) as count
    FROM tags t 
        LEFT JOIN tags_relationships r ON r.tag_id = t.id
    GROUP BY t.id
    ORDER BY count DESC
");

$search_term = trim($_GET['s'] ?? '');

?>
<div>
    <h3>Štítky</h3>
    <p>Slider všech štítků na webu.</p>
    <div id="tags-carousel" class="position-relative mb-5">
        <div class="carousel-inner">
            <?php
            foreach ($tags as $i => $tag):
                ?>
                <div class="carousel-item">
                    <a href="/Stitek/<?= ucfirst($tag['slug']) ?>">
                        <div class="img-wrapper">
                            <img src="<?= $tag['image'] ?>" class="h-100 w-100" alt="<?= $tag['name'] ?>">
                        </div>
                        <div class="carousel-caption p-1 pb-0">
                            <h5 class="text-white"><?= $tag['name'] ?> (<?= $tag['count'] ?>)</h5>
                            <p class="small"><?= get_excerpt($tag['description'], 50) ?></p>
                        </div>
                    </a>
                </div>
            <?php
            endforeach;
            ?>
        </div>
        <button class="carousel-control-prev" type="button" data-bs-target="#tags-carousel"
                data-bs-slide="prev">
            <span class="carousel-control-prev-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Předchozí</span>
        </button>
        <button class="carousel-control-next" type="button" data-bs-target="#tags-carousel"
                data-bs-slide="next">
            <span class="carousel-control-next-icon" aria-hidden="true"></span>
            <span class="visually-hidden">Další</span>
        </button>
    </div>
    <h3 id="clanky">Články
        <a class="read-random-btn" href="/Clanek/<?= $random_post_slug ?>"
           data-bs-toggle="tooltip" data-bs-placement="right" title="Číst náhodný">
            <i class="fas fa-dice"></i>
        </a>
    </h3>
    <p>Filtrujte články fulltextovým hledáním nebo podle štítků. Alternativně můžete začít číst náhodný článek <a
                href="/Clanek/<?= $random_post_slug ?>">zde</a>.</p>
    <form class="d-flex" id="article-search-form">
        <input class="form-control me-2" name="s" type="search" placeholder="Hledat mezi články..."
               aria-label="Hledat mezi články..." value="<?= $search_term ?>">
        <button class="btn btn-outline-success" type="submit">Hledat</button>
    </form>
    <hr>
    <div id="posts-archive">
        <?php
        $whereClause = "";

        if (!empty($search_term))
            $whereClause = "WHERE MATCH (p.title, p.content) AGAINST (%s IN NATURAL LANGUAGE MODE)";

        $sql = '
            SELECT p.*, COUNT(c.comment_id) AS comments_count, k.karma AS karma
            FROM posts AS p
                     LEFT JOIN comments AS c ON p.id = c.post_id
                     LEFT JOIN (SELECT obj_id, SUM(value) AS karma FROM karma WHERE type = "post" GROUP BY obj_id) AS k
                               ON k.obj_id = p.id
            ' . $whereClause . '
            GROUP BY p.id
        ';

        if (!empty($search_term)) {
            $search_term = preg_replace('/[^\p{L}\p{N}_]+/u', ' ', $search_term);
            $search_term = preg_replace('/[+\-><\(\)~*\"@]+/', ' ', $search_term);
            $posts = $db->query($sql, $search_term);
        }
        else {
            $sql .= 'ORDER BY p.date DESC';
            $posts = $db->query($sql);
        }

        if (count($posts))
            posts_archive_loop($posts, 250);
        else {
            ?>
            <div class="text-center">
                <strong>Nebylo nic nalezeno.</strong> Ujistěte se, že hledáte podle celých slov a nepoužíváte slova
                příliš krátká.
            </div>
            <?php
        }
        ?>
    </div>
    <endora>
</div>