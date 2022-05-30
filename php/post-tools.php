<?php

class PostTools
{
    public static function getExcerpt($text, $max_length = 140, $cut_off = '...', $keep_word = true): string
    {
        $text = strip_tags(trim($text));

        if (mb_strlen($text) <= $max_length) {
            return $text;
        }

        if ($keep_word) {
            $text = mb_substr($text, 0, $max_length + 1);

            if ($last_space = mb_strrpos($text, ' ')) {
                $text = mb_substr($text, 0, $last_space);
                $text = rtrim($text);
                $text .= $cut_off;
            }
        } else {
            $text = mb_substr($text, 0, $max_length);
            $text .= $cut_off;
        }

        return $text;
    }

    public static function getNiceDate($date): string
    {
        $months = [
            1  => "Led",
            2  => "Úno",
            3  => "Bře",
            4  => "Dub",
            5  => "Kvě",
            6  => "Čvn",
            7  => "Čvc",
            8  => "Srp",
            9  => "Zář",
            10 => "Říj",
            11 => "Lis",
            12 => "Pro"
        ];
        $date   = new \DateTime($date);

        return $date->format('j') . ' ' . $months[$date->format('n')] . ' ' . $date->format('Y') . ' v ' . $date->format('G:i');
    }

    public static function getCommentNoun($c): string
    {
        $comments_in_czech = "Komentářů";

        if ($c == 1) {
            $comments_in_czech = "Komentář";
        } elseif ($c > 1 && $c < 5) {
            $comments_in_czech = "Komentáře";
        }

        return $comments_in_czech;
    }

    public static function printArchiveLoop($posts, $excerpt_length = 180, $img_size = 'w250')
    {
        $db = App::getDb();
        foreach ($posts as $post):
            $tags = $db->query('SELECT t.name, t.slug
                    FROM tags AS t
                        INNER JOIN tags_relationships tr on t.id = tr.tag_id
                    WHERE tr.post_id = %i ', $post['id']);

            $tags_html = implode(', ', array_map(function ($tag) {
                return "<a href=\"/Stitek/" . ucfirst($tag['slug']) . "\">" . $tag['name'] . "</a>";
            }, $tags)) ?: "Žádné";

            if ($post['karma'] === null) {
                $post['karma'] = 0;
            }

            ?>
            <div class="row">
                <div class="content col-8">
                    <div class="tags">
                        <i class="fas fa-tags"></i> <?= $tags_html ?>
                    </div>
                    <a href="/Clanek/<?= ucfirst($post['slug']) ?>" class="text-reset text-decoration-none">
                        <h3><?= $post['title'] ?></h3>
                        <p>
                            <?= self::getExcerpt($post['content'], $excerpt_length) ?>
                        </p>
                    </a>
                    <div class="info-icons">
                        <div>
                            <i class="far fa-calendar-alt"></i> <?= self::getNiceDate($post['date']) ?>
                        </div>
                        <div>
                            <i class="far fa-comment"></i> <?= $post['comments_count'] ?> <?= self::getCommentNoun($post['comments_count']) ?>
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
                        <img src="<?= self::getImageSize($post['image'], $img_size) ?>" alt="<?= $post['title'] ?>"
                             class="w-100">
                    </a>
                </div>
            </div>
        <?php
        endforeach;
    }

    public static function getImageSize($image, $size): string
    {
        if ($size === 'largest') {
            $org = self::getImageSize($image, 'ORG');

            if (file_exists(__DIR__ . '/../' . $org)) {
                return $org;
            } else {
                return self::getImageSize($image, 'w1920');
            }
        }

        $parts     = explode('.', $image);
        $extension = array_pop($parts);
        $filename  = implode('.', $parts);

        return $filename . '-' . $size . '.' . $extension;
    }

    public static function getCurrentUrl()
    {
        return strtok(self::getSiteUrl() . "$_SERVER[REQUEST_URI]", '?');
    }

    public static function getSiteUrl(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]";
    }
}