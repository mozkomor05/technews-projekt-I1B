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
            <div>
                <h3>Avatar</h3>
                <div class="row mt-4">
                    <div class="col-3">
                        <img alt="profile avatar" class="img-fluid" src="<?= $userAvatar ?>">
                    </div>
                    <div class="col">
                        <form id="avatar-upload-form">
                            <div class="form-group">
                                <div class="alert-container">
                                    <div class="alert-primary alert fade show" style="display: none">
                                        <div class="content">
                                            Nahrávání <span class="progress-percentage">0</span>%
                                        </div>
                                    </div>
                                    <div class="alert-danger alert fade show" style="display: none">
                                        <div class="content"></div>
                                    </div>
                                </div>
                                <label for="avatar" class="form-label">Vyberte profilový obrázek do velikosti 600
                                    Kb.</label>
                                <input type="hidden" name="MAX_FILE_SIZE" value="614400"/>
                                <input class="form-control" type="file" id="avatar" name="avatar" accept="image/*"
                                       required>
                            </div>
                            <div class="form-row mt-3">
                                <button type="submit" class="btn btn-primary">Nahrát</button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
