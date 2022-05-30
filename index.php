<?php

require_once(__DIR__ . '/php/app_load.php');

$config = App::getConfig();

function throw_err_404(): bool
{
    http_response_code(404);
    include 'templates/404.php';

    return false;
}

$part = strtolower($_GET['template'] ?? 'index');
$part = str_replace('/', '_', $part);

if (empty($part)) {
    $part = 'index';
}

$part_path = __DIR__ . "/templates/{$part}.php";

ob_start();

if ($part == "404" || ! file_exists($part_path)) {
    throw_err_404();
} else {
    include $part_path;
}

$page_data    = $GLOBALS['page_data'] ?? [];
$page_content = ob_get_clean();

$css_file = 'assets/css/index' . ($config->get('debug') ? '' : '.min') . '.css';
$css_path = '/' . $css_file . '?=' . filemtime('./' . $css_file);

?>
<!DOCTYPE html>
<html lang='cs'>
<head>
    <meta charset='utf-8'>
    <meta name="author" content="David Moškoř">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/5.15.4/css/all.min.css">
    <?php
    foreach ($page_data['styles'] ?? [] as $style):
        ?>
        <link rel="stylesheet" href="<?= $style ?>">
    <?php
    endforeach;
    ?>
    <link href="<?= $css_path ?>" rel="stylesheet">

    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="icon" href="/favicon.ico?v=2"/>
    <link rel="manifest" href="/site.webmanifest">
</head>
<body>
<?php
if ($page_data['main_page'] ?? false) {
    get_main_header();
} else {
    get_header($page_data);
}
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
                    Strojové učení, fyzika, matematika, IT průmysl... Pokrok v těchto oblastech stoupá exponenciálně a
                    sledovat jej můžete zde.
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

<!-- Registration Modal -->
<div class="modal fade" id="registrationModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="registration-form" class="needs-validation" novalidate
                  oninput='passwordConfirm.setCustomValidity(passwordConfirm.value !== password.value ? "Hesla se neshodují." : "")'>
                <div class="alert-container">
                    <div class="error-alert alert alert-danger alert-dismissible fade show" role="alert"
                         style="display: none">
                        <div class="content"></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title">Registrace</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <p>Pro vytvoření účtu prosím vyplňte registrační formulář.</p>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="text" class="form-control " id="registration_form-userName"
                                   placeholder="Uživatelské jméno"
                                   pattern="^[a-zA-Z0-9]([._-](?![._-])|[a-zA-Z0-9]){1,28}[a-zA-Z0-9]$"
                                   minlength="3" max="30" name="userName" required aria-describedby="help_block-name">
                            <label for="registration_form-userName">Uživatelské jméno</label>
                            <div class="invalid-feedback">
                                Uživatelské jméno délší tří znaků je povinné. Povolené znaky jsou písmena bez diakritiky
                                a čísla. Dále jsou povoleny pomlčky, tečky a podtržítka, nesmí se jimi však začínat nebo
                                končit.
                            </div>
                            <div id="help_block-name" class="form-text">
                                Unikátní přezdívka, pod kterou se budete přihlašovat.
                            </div>
                        </div>
                    </div>
                    <div class="form-row row mb-3">
                        <div class="col">
                            <div class="form-floating">
                                <input type="text" class="form-control " id="registration_form-firstName"
                                       placeholder="Jméno"
                                       minlength="2" max="60" name="firstName" required>
                                <label for="registration_form-firstName">Jméno</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-floating">
                                <input type="text" class="form-control " id="registration_form-lastName"
                                       placeholder="Přijmení" required
                                       minlength="2" max="60" name="lastName">
                                <label for="registration_form-lastName">Přijmení</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="email" class="form-control " id="registration_form-email"
                                   placeholder="E=mail" required aria-describedby="help_block-email"
                                   minlength="3" max="40" name="email">
                            <label for="registration_form-email">E-mail</label>
                            <div id="help_block-email" class="form-text">
                                Ve výchoyím nastavení nebude vaše e-mailová adresa zobrazena.
                            </div>
                            <div class="invalid-feedback">
                                Prosím zadejte validní e-mail.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="registration_form-password"
                                   placeholder="Heslo" minlength="6" max="70" name="password" required>
                            <label for="registration_form-email">Heslo</label>
                            <div class="invalid-feedback">
                                Prosím zadejte heslo o alespoň 6 znacích.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="registration_form-password_confirm"
                                   placeholder="Potvrďte heslo" max="70" required name="passwordConfirm">
                            <label for="registration_form-registration_form-password_confirm">Potvrďte heslo</label>
                            <div class="invalid-feedback">
                                Hesla se neshodují.
                            </div>
                        </div>
                    </div>
                    <div class="form-row mb-3">
                        <div class="g-recaptcha" data-sitekey="<?= $config->get(['recaptcha', 'site']) ?>"
                             data-callback="grecaptchaValidated"></div>
                        <div class="invalid-feedback">
                            Proveďte ověření.
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Registrovat</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Log-in Modal -->
<div class="modal fade" id="loginModal" tabindex="-1" role="dialog">
    <div class="modal-dialog">
        <div class="modal-content">
            <form id="login-form" class="needs-validation" novalidate>
                <div class="alert-container">
                    <div class="error-alert alert alert-danger alert-dismissible fade show" role="alert"
                         style="display: none">
                        <div class="content"></div>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"
                                aria-label="Close"></button>
                    </div>
                </div>
                <div class="modal-header">
                    <h5 class="modal-title">Přihlášení</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <div class="modal-body">
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="text" class="form-control " id="login_form-userName"
                                   placeholder="Uživatelské jméno" max="30" name="userName" required>
                            <label for="login_form-userName">Uživatelské jméno nebo e-mail: </label>
                            <div class="invalid-feedback">
                                Zadejte prosím uživatelské jméno nebo email.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="login_form-password"
                                   placeholder="Heslo" max="70" name="password" required>
                            <label for="login_form-password">Heslo</label>
                            <div class="invalid-feedback">
                                Zadejte prosím heslo.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-check col mb-3">
                            <input type="checkbox" class="form-check-input" id="login_form-remember"
                                   name="remember" value="">
                            <label for="login_form-remember" class="form-check-label">Pamatovat si mě</label>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="submit" class="btn btn-primary">Přihlásit se</button>
                </div>
            </form>
        </div>
    </div>
</div>

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