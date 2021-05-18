<?php

function get_excerpt($content, $length = 40, $more = '...'): string
{
    $excerpt = strip_tags(trim($content));
    $words = str_word_count($excerpt, 2);
    if (count($words) > $length) {
        $words = array_slice($words, 0, $length, true);
        end($words);
        $position = key($words) + strlen(current($words));
        $excerpt = substr($excerpt, 0, $position) . $more;
    }
    return $excerpt;
}

function nice_date($date): string
{
    $months = [
        1 => "Led",
        2 => "Úno",
        3 => "Bře",
        4 => "Dub",
        5 => "Kvě",
        6 => "Čvn",
        7 => "Čvc",
        8 => "Srp",
        9 => "Zář",
        10 => "Říj",
        11 => "Lis",
        12 => "Pro"
    ];
    $date = new DateTime($date);

    return $date->format('j') . ' ' . $months[$date->format('n')] . ' ' . $date->format('Y') . ' v ' . $date->format('G:i');
}

function get_comments_noun($c): string
{
    $comments_in_czech = "Komentářů";

    if ($c == 1)
        $comments_in_czech = "Komentář";
    else if ($c > 1 && $c < 5)
        $comments_in_czech = "Komentáře";

    return $comments_in_czech;
}