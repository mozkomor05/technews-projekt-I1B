<?php
$config = json_decode(file_get_contents(__DIR__ . '/php/config.json'));

if ($config->debug) {
    ini_set('display_errors', '1');
    ini_set('display_startup_errors', '1');
    error_reporting(E_ALL);
}

require_once(__DIR__ . "/vendor/autoload.php");

$db = new MeekroDB($config->db->host, $config->db->user, $config->db->password, $config->db->database, null, "utf8");

require_once(__DIR__ . '/php/post-tools.php');
require_once(__DIR__ . '/php/headers.php');
require_once(__DIR__ . '/php/sidebar.php');

function throw_err_404()
{
    http_response_code(404);
    include 'templates/404.php';
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
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
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
            <div class="col-12 col-md-6">
                <div class="h3">O stránce</div>
                <p>
                    Lorem ipsum dolor sit amet, consectetuer adipiscing elit. Suspendisse sagittis ultrices augue. Cum
                    sociis
                    natoque penatibus et magnis dis parturient montes, nascetur ridiculus mus. Fusce dui leo, imperdiet
                    in, aliquam
                    sit amet, feugiat eu, orci. Fusce wisi.
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
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
<script src="/assets/js/parallax.min.js"></script>
<script src="/assets/js/index.js?v=<?= filemtime("./assets/js/index.js") ?>"></script>
</body>
</html>