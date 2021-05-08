<?php
$part = $_GET['part'];


?>
<!DOCTYPE html>
<html lang='cs'>
<head>
    <meta charset='utf-8'>
    <meta name="author" content="David Moškoř">

    <title><?=$page_info['title']?> - TechNews</title>

    <link href="https://fonts.googleapis.com/css2?family=Roboto:ital,wght@0,300;0,400;0,500;1,300;1,400;1,500&display=swap"
          rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/css/bootstrap.min.css"
          integrity="sha384-TX8t27EcRE3e/ihU7zmQxVncDAy5uIKz4rEkgIXeMed4M0jlfIDPvg6uqKI2xXr2" crossorigin="anonymous">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.3/css/all.min.css">
    <link href="/assets/css/index.css?=<?php echo filemtime('./assets/css/index.css') ?>" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
<header>
    <nav class="navbar navbar-expand-lg navbar-dark py-3 px-5">
        <a class="navbar-brand mb-0 h1" href="/">
            <img src="/assets/img/icon.png" width="30" height="30" class="d-inline-block align-top" alt="">
            TechNews
        </a>
        <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-content">
            <span class="navbar-toggler-icon"></span>
        </button>

        <div class="collapse navbar-collapse" id="navbar-content">
            <ul class="navbar-nav ml-auto mr-auto">
                <li class="nav-item active">
                    <a class="nav-link" href="/">Domů</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/Vypis-clanku">Výpis článků</a>
                </li>
                <li class="nav-item">
                    <a class="nav-link" href="/O-projektu">O projektu</a>
                </li>
                <li class="nav-item dropdown">
                    <a class="nav-link dropdown-toggle" href="#" id="navbarDropdown" role="button" data-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                        Kontakt
                    </a>
                    <div class="dropdown-menu" aria-labelledby="navbarDropdown">
                        <a class="dropdown-item" href="#">Osobní kontakt</a>
                        <div class="dropdown-divider"></div>
                        <a class="dropdown-item" href="https://www.spseiostrava.cz/">SPŠEI Ostrava</a>
                    </div>
                </li>
            </ul>
            <form class="form-inline my-2 my-lg-0">
                <input class="form-control mr-sm-2" type="search" placeholder="Hledat mezi články..."
                       aria-label="Search">
                <button class="btn btn-outline-primary my-2 my-sm-0 search-btn" type="submit">Hledat</button>
            </form>
        </div>
    </nav>
    <div class="container py-5 main">
        <div class="content">
            <div class="h1">Novinky ze světa techniky</div>
            <p class="mt-5">Strojové učení, fyzika, matematika, IT průmysl... Pokrok v těchto oblastech stoupá exponenciálně a sledovat jej můžete zde.</p>
            <a href="#" class="btn px-4">Objevit <i class="fas fa-arrow-right"></i></a>
        </div>
    </div>
</header>

<main class="container py-5">
    <div class=" row">
        <div class="col-8">
            <h1>Nejnovější</h1>
            <p>Seznam nejnovějších článků.</p>
        </div>
        <div class="sidebar col-4">
            <div class="tags">
                <div class="h5">Štítky</div>
                <ul>
                    <li>Machine learning</li>
                    <li>Math</li>
                    <li>Data Sceince</li>
                </ul>
            </div>
            <div>
                <div class="h5">Oblíbené</div>
                <p>zde přijde seznam aktuálních článků</p>
            </div>
            <div id="advertisement">
                <div class="h5">Reklama</div>
                <endora>
            </div>
        </div>
    </div>
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
        &copy; Vytvořil David Moškoř 2020
    </div>
</footer>

<script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.5.1/jquery.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.3/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ho+j7jyWK8fNQe+A12Hb8AhRq26LrZ/JpcUGGOn+Y7RsweNrtN/tE3MoK7ZeZDyx"
        crossorigin="anonymous"></script>
<script src="/assets/js/index.js"></script>
</body>
</html>