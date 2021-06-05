<?php
$GLOBALS['page_data'] = [
    'title' => 'Osobní kontakt - TechNews',
    'header' => [
        'active_index' => 3,
        'image' => '/assets/img/graphics/header.jpg',
        'title' => 'Osobní kontakt'
    ]
];
?>
<div>
    <h1>Osobní kontakt</h1>
    <div>Kontaktovat mě můžete pomocí kontakního formuláře níže.</div>
    <hr>
    <form id="contact-form" class="needs-validation mb-5" novalidate>
        <div class="fw-bold mb-3 fs-3">Kontaktní formulář</div>
        <div class="form-row">
            <div class="form-floating col mb-3">
                <input type="text" class="form-control " id="comment_form-name" placeholder="Jméno"
                       minlength="3" max="40" name="name" required aria-describedby="help_block-name">
                <label for="comment_form-name">Jméno</label>
                <div class="invalid-feedback">
                    Jméno (délší tří znaků) je povinné.
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-floating col mb-3">
                <input type="email" class="form-control" id="comment_form-email" placeholder="E-mail"
                       name="email" aria-describedby="help_block-email" required>
                <label for="comment_form-email">E-mail</label>
                <div class="invalid-feedback">
                    Prosím zadejte validní e-mail.
                </div>
            </div>
        </div>
        <div class="form-row">
            <div class="form-floating col mb-3">
                                <textarea class="form-control" id="comment_form-message" maxlength="2000"
                                          name="message" placeholder="Vaše zpráva..." required></textarea>
                <label for="comment_form-message">Zpráva</label>
                <div class="invalid-feedback">
                    Vaše zpráva nemůže být prázdná.
                </div>
            </div>
        </div>
        <div class="form-row mb-3">
            <div class="g-recaptcha" data-sitekey="<?= $config->recaptcha->site ?>"
                 data-callback="grecaptchaValidated"></div>
            <div class="invalid-feedback">
                Proveďte ověření.
            </div>
        </div>
        <button class="btn btn-primary" type="submit">Odeslat</button>
    </form>
    <endora>
</div>