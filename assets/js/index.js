function grecaptchaValidated() {
    const $recaptcha = $('.g-recaptcha');
    $recaptcha.removeClass("is-invalid");
    $recaptcha.addClass("is-valid");
}

$(document).ready(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();

    window.headerOuterHeight = $('header nav.navbar').outerHeight(true);

    $('form.needs-validation').each(function () {
        this.addEventListener('submit', function (event) {
            const $this = $(this);
            const $recaptcha = $this.find('.g-recaptcha');
            const failedRecaptcha = $recaptcha.length && !grecaptcha.getResponse().length;

            if (!this.checkValidity() || failedRecaptcha) {
                if (failedRecaptcha) {
                    $recaptcha.addClass("is-invalid");
                    $recaptcha.removeClass("is-valid");
                } else
                    grecaptchaValidated.apply($recaptcha);

                event.preventDefault()
                event.stopPropagation()
            }

            $this.addClass('was-validated')
        }, true);
    });

    $('body').on('click', 'a.js-anchor', function (event) {
        event.preventDefault();
        scrollToElement($(this.hash));
    })

    setupArticle();

    setupTagCarousel();

    setupArticleSearchForm();

    setupPhotoGalleries();

    setupRegistrationForm();

    setupLoginForm();

    setupLogout();
});

function scrollToElement(element, speed = 1000) {
    $([document.documentElement, document.body]).animate({
        scrollTop: element.offset().top - window.headerOuterHeight,
    }, speed);
}

function setupArticle() {
    const $readingTime = $('.reading-time'),
        $articleWrapper = $('#article-content'),
        $articleContent = $articleWrapper.find('.content');

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

        $.post("/php/ajax.php", {
            action: "process_vote",
            post_id: $articleWrapper.attr('data-id'),
            vote: $this.has('.fa-thumbs-up').length ? 1 : -1,
            type: 'post'
        }, function (res) {
            if (res.success) {
                const $votes = $this.next('.votes');

                $this.addClass("animate");
                $votes.text(parseInt($votes.text()) + 1);
                $parent.addClass('voted');
            }
        });
    });

    const $commentsWrapper = $('#article-comments');

    if ($commentsWrapper.length) {
        const $commentsSection = $commentsWrapper.find('.comments-section');
        const $showMore = $commentsWrapper.find('.show-more button');
        const $commentForm = $commentsWrapper.find('#comment-form');
        const $replyToInput = $commentForm.find('#reply-to');

        const $commentTemplateEl = $commentsSection.children('.comment');
        const $commentTemplate = $commentTemplateEl.clone();
        const $alertContainer = $commentsWrapper.find('#alert-container');
        const $replyAlertEl = $alertContainer.children('.reply-alert');
        const $replyAlertTemplate = $replyAlertEl.clone();
        const $errorAlertEl = $alertContainer.children('.error-alert');
        const $errorAlertTemplate = $errorAlertEl.clone();

        $commentTemplateEl.remove();
        $replyAlertEl.remove();
        $errorAlertEl.remove();

        $.post('/php/ajax.php', {
            action: 'list_comments',
            post_id: $articleWrapper.attr('data-id')
        }, "json").done(res => {
            const prepareCommentsArray = function (flatComments) {
                const comments = [], childrenComments = [];

                flatComments.forEach(comment => {
                    if (comment['reply'] === null) {
                        comment.children = [];
                        comments.push(comment);
                    } else
                        childrenComments.push(comment);
                })

                childrenComments.forEach(childComment => {
                    let reply = childComment;
                    let parentId = reply['reply'];


                    while (true) {
                        const parent = childrenComments.find(c => {
                            return c['comment_id'] === parentId;
                        });

                        if (parent === undefined)
                            break;

                        parentId = parent['reply'];
                    }

                    const parent = comments.find(c => {
                        return c['comment_id'] === parentId;
                    });

                    parent.children.unshift(reply);
                });

                return comments;
            }

            const comments = prepareCommentsArray(JSON.parse(res));

            const commentElFromTemplate = function (data) {
                const $comment = $commentTemplate.clone();

                $comment.attr('data-id', data['comment_id']);
                $comment.attr('id', 'comment-' + data['comment_id']);
                $comment.find('.image').attr('src', data['image_src']);
                $comment.find('.date').text(data['created_formatted']);
                $comment.find('.name').html(data['author_name']);
                $comment.find('.content').text(data['content']);

                if (data['reply'] !== null) {
                    $comment.addClass('reply');
                    const $replyLink = $comment.find('.reply-to-comment');
                    $replyLink.attr('href', '#comment-' + data['reply'])
                    $replyLink.text($('#comment-' + data['reply']).find('.name').text());

                    $replyLink.on('click', function () {
                        const $highlightComment = $(this.hash);

                        if ($highlightComment.hasClass("highlight"))
                            return;

                        $highlightComment.addClass("highlight");
                        setTimeout(function () {
                            $highlightComment.removeClass("highlight");
                        }, 2000);
                    });
                }

                if (data['is_user']) {
                    $comment.find('.comments-section-edit-btn').show();
                    $comment.find('.comments-section-delete-btn').show();
                }

                $comment.show();

                return $comment;
            }
            const loadCommentsBatch = function (batchSize = 10) {
                for (let i = 0; i < batchSize; i++) {
                    if (!comments.length)
                        break;

                    const comment = comments.shift();
                    const $comment = commentElFromTemplate(comment);

                    $comment.appendTo($commentsSection);

                    comment.children.forEach(reply => {
                        const $reply = commentElFromTemplate(reply);
                        $reply.appendTo($commentsSection);
                    });
                }

                return comments.length > 0;
            };
            const loadMore = function (num = 10) {
                if (loadCommentsBatch(num))
                    $showMore.show();
                else
                    $showMore.hide();
            }

            if (!comments.length) {
                $commentsSection.text("Zatím tu nejsou žádné komentáře. Buďte první, kdo se podělí o svůj názor!");
            } else {
                $showMore.on('click', function () {
                    loadMore(10);
                });

                loadMore(5);
            }

            $('body').on('click', '.comments-section-reply-btn', function () {
                const $commentEl = $(this).closest('.comment');
                const $commentId = $commentEl.attr('data-id');

                const $alert = $replyAlertTemplate.clone();
                const $alertLink = $alert.find('.reply-to');

                $replyToInput.val($commentId);

                $alertLink.text($commentEl.find('.name').text());
                $alertLink.attr('href', '#comment-' + $commentId);
                $alert.appendTo($alertContainer);
                $alert.fadeIn();

                $alert.on('close.bs.alert', function () {
                    $replyToInput.val("-1");
                });

                scrollToElement($commentForm);
            });

            $commentForm.submit(function (event) {
                event.preventDefault();

                const ajaxData = $commentForm.serializeArray();
                ajaxData.push({
                    name: 'action',
                    value: 'submit_comment'
                });
                ajaxData.push({
                    name: 'post_id',
                    value: $articleWrapper.attr('data-id')
                });

                $.post("/php/ajax.php", $.param(ajaxData)).done(submitRes => {
                    const resComment = JSON.parse(submitRes)['comment'];
                    const $comment = commentElFromTemplate(resComment);

                    if (resComment['reply'] === null)
                        $comment.prependTo($commentsSection);
                    else {
                        const $replyTo = $('#comment-' + resComment['reply']);
                        const $nextComment = $replyTo.nextAll(":not(.reply)").first();

                        if ($nextComment.length)
                            $comment.insertBefore($nextComment);
                        else
                            $comment.appendTo($commentsSection);
                    }

                    grecaptcha.reset();
                    $commentForm.trigger("reset");
                    $commentForm.removeClass('was-validated');

                    $alertContainer.empty();

                    scrollToElement($("#comment-" + resComment['comment_id']));
                }).fail(req => {
                    grecaptcha.reset();
                    let message = "Nepodařilo se odeslat komentář."

                    switch (req.responseText) {
                        case 'validation_failure':
                            message = "Nepodařilo se ověřit správnost některých polí. Ujištěte se, že jsou dostatečně dlouhá.";
                            break;

                        case 'email_validation_failure':
                            message = 'Tento e-mail neexistuje. Prosím zkontrolujte jej.';
                            break;
                    }

                    $alertContainer.find('.error-alert').remove();

                    const $alert = $errorAlertTemplate.clone();

                    $alert.find('.content').text(message);
                    $alert.prependTo($alertContainer);
                    $alert.fadeIn();
                });
            });
        });
    }
}

function setupTagCarousel() {
    const $tagsCarousel = $('#tags-carousel');

    if ($tagsCarousel.length) {
        const slider = $tagsCarousel.children('.carousel-inner').lightSlider({
            controls: false,
            loop: true,
            auto: true,
            slideMargin: 5,
            items: 3,
            pause: 6000,
            pager: false
        });

        $tagsCarousel.find('.carousel-control-next').on('click', function () {
            slider.goToNextSlide();
        });

        $tagsCarousel.find('.carousel-control-prev').on('click', function () {
            slider.goToPrevSlide();
        });
    }
}

function setupArticleSearchForm() {
    const $articleSearchForm = $('#article-search-form');

    if ($articleSearchForm.length) {
        const searchParams = new URLSearchParams(window.location.search)
        if (searchParams.has('s') && searchParams.get('s') && searchParams.get('s').trim())
            scrollToElement($('#clanky'), 300);
        else
            window.history.replaceState(null, null, window.location.pathname);

        $articleSearchForm.submit(function (ev) {
            ev.preventDefault();

            const url = window.location.protocol + "//" + window.location.host + window.location.pathname + "?" + $articleSearchForm.serialize();
            window.history.replaceState(null, null, url);
            document.location.reload(true);
        });
    }
}

function setupPhotoGalleries() {
    const $photoGalleries = $('.photo-gallery');

    if ($photoGalleries.length) {
        $('<link>')
            .appendTo('head')
            .attr({
                rel: 'stylesheet',
                href: 'https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.css'
            });

        $.getScript("https://cdn.jsdelivr.net/gh/fancyapps/fancybox@3.5.7/dist/jquery.fancybox.min.js", function (data, textStatus, jqxhr) {
            const $links = $photoGalleries.find('a');

            $links.attr("data-caption", function () {
                return $(this).find("img").attr("alt");
            });
            $links.attr("data-fancybox", "gallery")
            $links.fancybox();
        });
    }
}

function setupRegistrationForm() {
    const $registrationForm = $('#registration-form');
    const $alertContainer = $registrationForm.find('.alert-container');
    const $errorAlertEl = $alertContainer.children('.error-alert');
    const $errorAlertTemplate = $errorAlertEl.clone();

    $errorAlertEl.remove();

    $registrationForm.submit(function (ev) {
        ev.preventDefault();

        const ajaxData = $registrationForm.serializeArray();
        ajaxData.push({
            name: 'action',
            value: 'register'
        });

        $.post("/php/ajax.php", $.param(ajaxData)).done(submitRes => {
            window.location.reload();
        }).fail(req => {
            grecaptcha.reset();
            let message = "Nepodařilo se registrovat."

            switch (req.responseText) {
                case 'validation_failure':
                    message = "Nepodařilo se ověřit správnost některých polí. Ujištěte se, že jsou dostatečně dlouhá.";
                    break;

                case 'email_validation_failure':
                    message = 'Tento e-mail neexistuje. Prosím zkontrolujte jej.';
                    break;

                case 'user_name_already_exists':
                    message = 'Uživatel s tímto uživatelským jménem již existuje.';
                    break;

                case 'user_email_already_exists':
                    message = 'Uživatel s tímto e-mailem již existuje.';
                    break;
            }

            $alertContainer.find('.error-alert').remove();

            const $alert = $errorAlertTemplate.clone();

            $alert.find('.content').text(message);
            $alert.prependTo($alertContainer);
            $alert.fadeIn();
        });
    });
}

function setupLoginForm() {
    const $loginForm = $('#login-form');
    const $alertContainer = $loginForm.find('.alert-container');
    const $errorAlertEl = $alertContainer.children('.error-alert');
    const $errorAlertTemplate = $errorAlertEl.clone();

    $errorAlertEl.remove();

    $loginForm.submit(function (ev) {
        ev.preventDefault();

        const ajaxData = $loginForm.serializeArray();
        ajaxData.push({
            name: 'action',
            value: 'login'
        });

        $.post("/php/ajax.php", $.param(ajaxData)).done(submitRes => {
            window.location.reload();
        }).fail(req => {
            grecaptcha.reset();
            let message = "Nepodařilo se přihlásit."

            switch (req.responseText) {
                case 'not_valid':
                    message = "Neplatné přihlašovací údaje, ujistěte se, že jste je správně zadali.";
                    break;

                case 'validation_failure' :
                    message = 'Ujistěte se, že jste vyplnili všechna pole.';
                    break;
            }

            $alertContainer.find('.error-alert').remove();

            const $alert = $errorAlertTemplate.clone();

            $alert.find('.content').text(message);
            $alert.prependTo($alertContainer);
            $alert.fadeIn();
        });
    });
}

function setupLogout() {
    $('.logout-link').on('click', function (ev) {
        ev.preventDefault();

        $.post("/php/ajax.php", {
            action: 'logout',
        }).done(submitRes => {
            window.location.reload();
        });
    });
}