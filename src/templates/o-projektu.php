<?php
$GLOBALS['page_data'] = [
    'title' => 'O projektu - TechNews',
    'header' => [
        'active_index' => 2,
        'image' => '/assets/img/uploads/o-projektu.jpg',
        'title' => 'O projektu'
    ]
];
?>
<div>
    <h1>O projektu</h1>
    <p>
        Tento projekt je závěrečným projektem předmětu Zákaldy webových aplikací prvního ročníku. Chtěl jsem udělat
        blogový systém a zároveň se zajímám o technologie, proto jsem si zvolil toto téma. Články jsem však <strong>nenapsal
            sám</strong>, většinu jsem zkopíroval a mírně upravil obsah (nebo přidal nové odstravce apod.).
    </p>
    <p>
        Web je dynamicky sestaven - články jsou uloženy v databázi. Hlasování a komentáře probíhají AJAXem. Hlasování je
        omezeno IP adresou a cookies soubory. Komentáře jsou zabezpečeny Google ReCAPTCHou v2. V projektu používám
        nejmodernější funkce jazyku JavaScript a CSS3, proto bude nejspíš fungovat pouze v
        moderních prohlížěčích. Do jisté míry je stránka také responzivní.
    </p>
    <p>
        Celý projekt je Open Source a kódy jsou dostupné na následující adrese: <a
                href="https://github.com/mozkomor05/technews-projekt-I1B">https://github.com/mozkomor05/technews-projekt-I1B</a>
    </p>
    <div class="pb-5">
        <h3>Použité technologie</h3>
        <div class="fw-bold fs-4 mt-4">Frontend</div>
        <div class="used-technologies row gx-5 justify-content-center">
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/javascript.png" alt="JavaScript">
                <div class="title">JavaScript</div>
            </div>
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/jquery.png" alt="jQuery">
                <div class="title">jQuery 3</div>
            </div>
            <div class="col-sm-3 col-6">
                <img class="p-4"
                     src="/assets/img/graphics/css.png"
                     alt="CSS">
                <div class="title">CSS3</div>
            </div>
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/bootstrap.png" alt="Bootstrap">
                <div class="title">Bootstrap 5</div>
            </div>
        </div>
        <div class="mt-5">Dále <span class="fw-bold">Fancybox 3</span> a <span class="fw-bold">LightSlider</span>.</div>
        <div class="fw-bold fs-4 mt-5">Backend</div>
        <div class="used-technologies row gx-5 justify-content-center">
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/php.png"
                     alt="PHP 7.4">
                <div class="title">PHP 7.4</div>
            </div>
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/mysql.png" alt="MySQL">
                <div class="title">MySQL</div>
            </div>
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/python.png"
                     alt="Python" class="py-3">
                <div class="title">Python</div>
            </div>
            <div class="col-sm-3 col-6">
                <img src="/assets/img/graphics/apache.png"
                     alt="Apache">
                <div class="title">Apache</div>
            </div>
        </div>
        <div class="mt-5">Dále <span class="fw-bold">Composer</span> a <span class="fw-bold">MeekroDB</span>.</div>
    </div>
    <endora>
</div>