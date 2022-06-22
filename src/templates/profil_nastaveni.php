<?php

if ( ! LoginTools::isLoggedIn()) {
    header('Location: /');
    die();
}

$db         = App::getDb();
$user       = LoginTools::getUser();
$userAvatar = UserTools::getAvatar($user);

$GLOBALS['page_data'] = [
    'title'   => 'Můj profil - TechNews',
    'header'  => [
        'active_index' => 4,
        'image'        => '/assets/img/graphics/header.jpg',
        'title'        => sprintf(
            '<img src="%s" width="100" alt="profile avatar" class="profile-avatar"/>Nastvení',
            $userAvatar
        ),
    ],
    'scripts' => [
        'https://cdn.crop.guide/loader/l.js',
    ],
];

?>
<div class="row">
    <?php
    get_profile_sidebar(1);
    ?>
    <div class="col">
        <h1>Nastavení</h1>
        <div class="mt-5">
            <div class="mb-5">
                <h3>Osobí údaje</h3>
                <form class="needs-validation profile-details-form" novalidate>
                    <div class="alert-container mt-3">
                        <div class="error-alert alert alert-danger alert-dismissible fade show" role="alert"
                             style="display: none">
                            <div class="content"></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="form-row row mt-3">
                        <div class="col">
                            <div class="form-floating">
                                <input type="text" class="form-control " id="profileDetails-firstName"
                                       placeholder="Jméno" value="<?= $user->first_name ?>"
                                       minlength="2" max="60" name="firstName" required>
                                <label for="registration_form-firstName">Jméno</label>
                            </div>
                        </div>
                        <div class="col">
                            <div class="form-floating">
                                <input type="text" class="form-control " id="profileDetails-lastName"
                                       placeholder="Přijmení" required value="<?= $user->last_name ?>"
                                       minlength="2" max="60" name="lastName">
                                <label for="registration_form-lastName">Přijmení</label>
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <div class="form-floating col">
                            <input type="email" class="form-control " id="profileDetails-email"
                                   placeholder="E=mail" required aria-describedby="help_block-email"
                                   minlength="3" max="40" name="email" value="<?= $user->email ?>">
                            <label for="profileDetails-email">E-mail</label>
                            <div id="help_block-email" class="form-text">
                                Ve výchoyím nastavení nebude vaše e-mailová adresa zobrazena.
                            </div>
                            <div class="invalid-feedback">
                                Prosím zadejte validní e-mail.
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <button class="btn btn-secondary" type="reset">Reset</button>
                        <button type="submit" class="btn btn-primary">Uložit</button>
                    </div>
                </form>
            </div>
            <div class="mb-5">
                <h3>Avatar</h3>
                <div class="row mt-4">
                    <div class="col-3">
                        <img alt="profile avatar" class="img-fluid" src="<?= $userAvatar ?>" id="avatar-upload-preview">
                    </div>
                    <div class="col">
                        <form id="avatar-upload-form">
                            <div class="form-group">
                                <div class="alert-container">
                                    <div class="alert-primary alert fade show" style="display: none">
                                        <div class="content d-flex align-items-center">
                                            <div>
                                                Nahrávání <span class="progress-percentage">0</span>%
                                            </div>
                                            <div class="spinner-border ms-auto" role="status" aria-hidden="true"></div>
                                        </div>
                                    </div>
                                    <div class="alert-danger alert fade show" style="display: none">
                                        <div class="content"></div>
                                    </div>
                                </div>
                                <label for="avatar-upload" class="form-label">Vyberte profilový obrázek. Obrázek bude
                                    automaticky ořezán na čtverec a zmenšen.</label>
                                <input type="hidden" name="MAX_FILE_SIZE" value="1048576"/>
                                <input class="form-control" type="file" id="avatar-upload" name="avatar"
                                       accept="image/png, image/jpeg"
                                       required>
                            </div>
                            <div class="form-row mt-3">
                                <button class="btn btn-secondary" type="reset">Reset</button>
                                <button class="btn btn-danger" type="reset" data-bs-toggle="modal"
                                        data-bs-target="#removeAvatarModal">Vymazat
                                </button>
                                <button type="submit" class="btn btn-primary">Nahrát</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
            <div class="mb-5">
                <h3>Změna hesla</h3>
                <form class="needs-validation profile-details-form" novalidate
                      oninput='passwordConfirm.setCustomValidity(passwordConfirm.value !== password.value ? "Hesla se neshodují." : "")'>
                    <div class="alert-container mt-3">
                        <div class="error-alert alert alert-danger alert-dismissible fade show" role="alert"
                             style="display: none">
                            <div class="content"></div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"
                                    aria-label="Close"></button>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="profileDetails-password"
                                   placeholder="Heslo" name="currentPassword" required>
                            <label for="profileDetails-password">Stávající heslo</label>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="profileDetails-password"
                                   placeholder="Heslo" minlength="6" max="70" name="password" required>
                            <label for="profileDetails-password">Nové heslo</label>
                            <div class="invalid-feedback">
                                Prosím zadejte heslo o alespoň 6 znacích.
                            </div>
                        </div>
                    </div>
                    <div class="form-row">
                        <div class="form-floating col mb-3">
                            <input type="password" class="form-control" id="profileDetails-password_confirm"
                                   placeholder="Potvrďte heslo" max="70" required name="passwordConfirm">
                            <label for="profileDetails-password_confirm">Potvrďte nové heslo</label>
                            <div class="invalid-feedback">
                                Hesla se neshodují.
                            </div>
                        </div>
                    </div>
                    <div class="form-row mt-3">
                        <button type="submit" class="btn btn-primary">Změnit heslo</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="removeAvatarModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title" id="exampleModalLabel">Přejete si opravdu odstranit avatar?</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Zavřít"></button>
            </div>
            <div class="modal-body">
                Váš avatar bude odstraněn bez možnosti obnovy.
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Zavřít</button>
                <button class="btn btn-danger" type="reset" id="remove-avatar-btn">Vymazat</button>
            </div>
        </div>
    </div>
</div>
