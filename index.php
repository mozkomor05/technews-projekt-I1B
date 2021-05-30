<?php
require_once(__DIR__ . '/php/app_load.php');

function throw_err_404()
{
    http_response_code(404);
    include 'templates/404.php';
    return false;
}

$part = strtolower($_GET['template'] ?? 'index');

if (empty($part))
    $part = 'index';

$part_path = __DIR__ . "/templates/{$part}.php";

ob_start();

if ($part == "404" || !file_exists($part_path))
    throw_err_404();
else
    include $part_path;

$page_data = $GLOBALS['page_data'] ?? [];
$page_content = ob_get_clean();

?>
<!DOCTYPE html>
<html lang='cs'>
<head>
    <meta charset='utf-8'>
    <meta name="author" content="David Moškoř">
    <?php
    foreach ($page_data['og_meta'] ?? [] as $property => $content):
        ?>
        <meta property="og:<?= $property ?>" content="<?= $content ?>">
    <?php
    endforeach;
    ?>

    <title><?= $page_data['title'] ?? 'TechNews - Pokrok a inovace, vše na jednom místě' ?></title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap"
          rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/css/bootstrap.min.css" rel="stylesheet"
          integrity="sha384-+0n0xVW2eSR5OomGNYDnhzAbDsOXxcvSN1TPprVMTNDbiYZCxYbOOl7+AMvyTG2x" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <?php
    foreach ($page_data['styles'] ?? [] as $style):
        ?>
        <link rel="stylesheet" href="<?= $style ?>">
    <?php
    endforeach;
    ?>
    <link href="/assets/css/index.css?=<?php echo filemtime('./assets/css/index.css') ?>" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" href="/favicon.ico?v=2"/>
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
<?php
if ($page_data['main_page'] ?? false)
    get_main_header();
else
    get_header($page_data);
?>
<main class="py-5 <?= $page_data['no_container'] ?? false === true ? '' : 'container' ?>">
    <?= $page_content ?>
</main>


<footer class="mt-5">
    <div class="container py-5">
        <div class="row">
            <div class="col-12 col-md-6 pe-4">
                <div class="h3">O stránce</div>
                <p>
                    Strojové učení, fyzika, matematika, IT průmysl... Pokrok v těchto oblastech stoupá exponenciálně a sledovat jej můžete zde.
                </p>
                <p>
                    Tato stránka vznikla v rámci závěrečného projektu předmětu Základy webových aplikací.
                </p>
            </div>
            <div class="col-6 col-md-3 links">
                <div class="h3">Sociální sítě</div>
                <ul>
                    <li><i class="fab fa-github"></i> <a href="https://github.com/mozkomor05">GitHub</a></li>
                    <li><i class="fas fa-envelope"></i> E-mail</li>
                    <li><i class="fab fa-facebook"></i> Facebook</li>
                </ul>
            </div>
            <div class="col-6 col-md-3">
                <div class="h3"><i class="fas fa-rss"></i> RSS</div>
                <ul>
                    <li><a href="/">RSS článků</a></li>
                    <li><a href="/">RSS komentářů</a></li>
                </ul>
            </div>
        </div>
    </div>
    <div class="copyright w-100 text-center">
        &copy; Vytvořil David Moškoř 2021
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.0.1/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-gtEjrD/SeCtmISkJkNUaaKMoLD0//ElJ19smozuHV6z3Iehds+3Ulb9Bn9Plx0x4"
        crossorigin="anonymous"></script>
<script src="/assets/js/parallax.min.js"></script>
<?php
foreach ($page_data['scripts'] ?? [] as $script):
    ?>
    <script src="<?= $script ?>"></script>
<?php
endforeach;
?>
<script src="/assets/js/index.js?v=<?= filemtime("./assets/js/index.js") ?>"></script>
<script src="https://www.google.com/recaptcha/api.js" async defer></script>
</body>
</html>