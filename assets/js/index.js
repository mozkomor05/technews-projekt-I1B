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

    const $readingTime = $('.reading-time'),
        $articleContent = $('#article-content .content');

    if ($readingTime.length && $articleContent.length) {
        const readTime = Math.ceil($articleContent.text().split(/\s+/).length / 200);
        let noun = "minut";

        if (readTime === 1)
            noun = "minutu";
        else if (readTime > 1 && readTime < 5)
            noun = "minuty";

        $readingTime.text(readTime + " " + noun);
    }

    const $socialButtons = $('.share-social-buttons');

    if ($socialButtons.length) {
        const encodedUrl = encodeURIComponent($('meta[property="og:url"]').attr('content'));

        $socialButtons.find('.fa-facebook-square').on('click', function () {
            window.open(`https://www.facebook.com/sharer/sharer.php?u=${encodedUrl}`);
        });

        $socialButtons.find('.fa-twitter-square').on('click', function () {
            window.open(`https://twitter.com/intent/tweet?url=${encodedUrl}`);
        });

        $socialButtons.find('.fa-linkedin').on('click', function () {
            window.open(`https://www.linkedin.com/sharing/share-offsite/?url=${encodedUrl}`);
        });
    }

    $('#article-content .thumb-rating .icons').on('click', function () {
        const $this = $(this);
        const $parent = $this.closest('.thumb-rating');

        if ($parent.hasClass('voted'))
            return;

        $this.addClass("animate");
        $parent.addClass('voted');
    })
});