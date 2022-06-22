<?php

function get_navbar($active_index = 0)
{
    $active                = array_fill(0, 6, "");
    $active[$active_index] = 'active';
    ?>
    <nav class="navbar navbar-expand-lg navbar-dark py-3 px-5">
        <a class="navbar-brand mb-0" href="/">
            <img src="/assets/img/icon.png" width="30" height="30" class="d-inline-block align-top" alt="TechNews">
            TechNews
        </a>
        <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbar-content">
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
                    <a class="nav-link dropdown-toggle <?= $active[3] ?>" href="#" id="contact-dropdown"
                       role="button"
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
                <li class="nav-item ms-3">
                    <form action="/Vypis-clanku">
                        <div class="input-group">
                            <div class="form-outline">
                                <input type="search" placeholder="Hledat mezi články..." class="search-input" name="s"/>
                            </div>
                            <button type="submit" class="btn search-button">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </form>
                </li>
            </ul>
            <div class="navbar-nav ms-5">
                <?php
                if (LoginTools::isLoggedIn()):
                    $user = LoginTools::getUser();
                    ?>
                    <div class="nav-item dropdown">
                        <a href="#" class="nav-link dropdown-toggle <?= $active[4] ?: $active[5] ?>" role="button"
                           id="profile-dropdown"
                           data-bs-toggle="dropdown" aria-haspopup="true" aria-expanded="false">
                            <img src=""/>
                            <b><?= UserTools::getNiceName($user) ?></b>
                        </a>
                        <div class="dropdown-menu" aria-labelledby="profile-dropdown">
                            <a class="dropdown-item <?= $active[4] ?>" href="/Profil"><i class="fas fa-user"></i>Můj
                                profil</a>
                            <a class="dropdown-item <?= $active[5] ?>" href="/Profil/nastaveni"><i
                                        class="fas fa-tools"></i>Nastavení</a>
                            <div class="dropdown-divider"></div>
                            <a class="dropdown-item logout-link" href="#"><i class="fas fa-sign-out-alt"></i>Odhlásit se</a>
                        </div>
                    </div>
                <?php
                else:
                    ?>
                    <div class="nav-item fa-sm">
                        <a href="#" class="nav-link" data-bs-toggle="modal" data-bs-target="#loginModal">Přilášení</a>
                    </div>
                    <div class="nav-item ms-2">
                        <a href="#" class="nav-link btn btn-sm" data-bs-toggle="modal"
                           data-bs-target="#registrationModal">Registrace</a>
                    </div>
                <?php
                endif;
                ?>
            </div>
        </div>
    </nav>
    <?php
}

function get_main_header()
{
    ?>
    <header>
        <?php
        get_navbar();
        ?>
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
    $bg_image    = $header_data['image'] ?? '/assets/img/graphics/header.jpg';
    ?>
    <header class="small-header <?= $show_header ? "" : "h-auto" ?>" <?php
    if ($show_header): ?>
            data-parallax="scroll" data-image-src="<?= $bg_image ?>" data-position-y="-100%">
        <?php
        else:
            echo ">";
        endif;

        get_navbar($header_data['active_index'] ?? -1);
        ?>
        <?php
        if ($show_header):
            ?>
            <div class="overlay w-100 d-flex h-100 align-items-center justify-content-center mx-auto w-100">
                <div class="h1 d-flex align-items-center"><?= $header_data['title'] ?? "" ?></div>
            </div>
        <?php
        endif;
        ?>
    </header>
    <?php
}