$(document).ready(function () {
    const headerOuterHeight = $('header nav.navbar').outerHeight(true);

    $('a.js-anchor').each(function () {
        $(this).on('click', function (event) {
            event.preventDefault();

            $([document.documentElement, document.body]).animate({
                scrollTop: $(this.hash).offset().top - headerOuterHeight
            }, 1000);
        })
    });
});