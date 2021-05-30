<?php

function get_main_header()
{
    ?>
    <header>
        <nav class="navbar navbar-expand-lg navbar-dark py-3 px-5">
            <a class="navbar-brand mb-0" href="/">
                <img src="/assets/img/icon.png" width="30" height="30" class="d-inline-block align-top" alt="">
                TechNews
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-content">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar-content">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item active">
                        <a class="nav-link" href="/">Domů</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/Vypis-clanku">Výpis článků</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="/O-Projektu">O projektu</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle" href="#" id="contact-dropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Kontakt
                        </a>
                        <div class="dropdown-menu" aria-labelledby="contact-dropdown">
                            <a class="dropdown-item" href="/Osobni-kontakt">Osobní
                                kontakt</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="https://www.spseiostrava.cz/">SPŠEI Ostrava</a>
                        </div>
                    </li>
                </ul>
                <form class="d-flex" action="/Vypis-clanku">
                    <input class="form-control me-2" type="search" placeholder="Hledat mezi články..."
                           aria-label="Hledat" name="s">
                    <button class="btn btn-outline-primary my-2 my-sm-0 search-btn" type="submit">Hledat</button>
                </form>
            </div>
        </nav>
        <div class="container py-5 main main-header-overlay">
            <div class="content">
                <div class="h1">Novinky ze světa techniky</div>
                <p class="mt-5">Strojové učení, fyzika, matematika, IT průmysl... Pokrok v těchto oblastech stoupá
                    exponenciálně a sledovat jej můžete zde.</p>
                <a href="#newest-anchor" class="btn px-4 js-anchor">Objevit <i class="fas fa-arrow-right"></i></a>
            </div>
        </div>
    </header>
    <?php
}

function get_header($page_data)
{
    $header_data = $page_data['header'] ?? [];
    $show_header = ($header_data['hide_header'] ?? false) !== true;
    $bg_image = $header_data['image'] ?? '/assets/img/graphics/header.jpg';
    $active = array_fill(0, 4, "");

    if (isset($header_data['active_index']))
        $active[$header_data['active_index']] = "active";
    ?>
    <header class="small-header <?= $show_header ? "" : "h-auto" ?>"
        <?php if ($show_header): ?>
            data-parallax="scroll" data-image-src="<?= $bg_image ?>" data-position-y="-100%"
        <?php endif; ?>>
        <nav class="navbar navbar-expand-lg navbar-dark py-3 px-5">
            <a class="navbar-brand mb-0" href="/">
                <img src="/assets/img/icon.png" width="30" height="30" class="d-inline-block align-top" alt="">
                TechNews
            </a>
            <button class="navbar-toggler" type="button" data-toggle="collapse" data-target="#navbar-content">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="navbar-content">
                <ul class="navbar-nav mx-auto">
                    <li class="nav-item">
                        <a class="nav-link <?= $active[0] ?>" href="/">Domů</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active[1] ?>" href="/Vypis-clanku">Výpis článků</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link <?= $active[2] ?>" href="/O-Projektu">O projektu</a>
                    </li>
                    <li class="nav-item dropdown">
                        <a class="nav-link dropdown-toggle <?= $active[3] ?>" href="#" id="contact-dropdown" role="button"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            Kontakt
                        </a>
                        <div class="dropdown-menu" aria-labelledby="contact-dropdown">
                            <a class="dropdown-item <?= $active[3] ?>" href="/Osobni-kontakt">Osobní
                                kontakt</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item" href="https://www.spseiostrava.cz/">SPŠEI Ostrava</a>
                        </div>
                    </li>
                </ul>
                <form class="d-flex" action="/Vypis-clanku">
                    <input class="form-control me-2" type="search" placeholder="Hledat mezi články..."
                           aria-label="Hledat" name="s">
                    <button class="btn btn-outline-primary my-2 my-sm-0 search-btn" type="submit">Hledat</button>
                </form>
            </div>
        </nav>
        <?php
        if ($show_header):
            ?>
            <div class="overlay w-100 d-flex h-100 align-items-center justify-content-center mx-auto w-100">
                <div class="h1"><?= $header_data['title'] ?? "" ?></div>
            </div>
        <?php
        endif;
        ?>
    </header>
    <?php
}