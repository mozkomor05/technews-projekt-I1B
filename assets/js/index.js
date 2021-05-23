$(document).ready(function () {
    $('[data-bs-toggle="tooltip"]').tooltip();

    const headerOuterHeight = $('header nav.navbar').outerHeight(true);

    $('body').on('click', 'a.js-anchor', function (event) {
        event.preventDefault();

        $([document.documentElement, document.body]).animate({
            scrollTop: $(this.hash).offset().top - headerOuterHeight
        }, 1000);
    })

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
    })

    $('form').submit(function (event) {
        if (!this.checkValidity()) {
            event.preventDefault()
            event.stopPropagation()
        }

        $(this).addClass('was-validated')
    });

    const $commentsWrapper = $('#article-comments');

    if ($commentsWrapper.length) {
        const $commentsSection = $commentsWrapper.find('.comments-section');
        const $commentTemplateEl = $commentsSection.children('.comment');
        const $commentTemplate = $commentTemplateEl.clone();
        $commentTemplateEl.remove();

        $.post('/php/ajax.php', {
            action: 'list_comments',
            post_id: $articleWrapper.attr('data-id')
        }, "json").done(res => {
            const flatComments = JSON.parse(res), comments = [], childrenComments = [];

            flatComments.forEach(comment => {
                if (comment['reply'] === null) {
                    comment.children = [];
                    comments.push(comment);
                }
                else
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

            const commentElFromTemplate = function (data) {
                const src = 'https://www.gravatar.com/avatar/' + data['email_hash'] + '?d=wavatar&s=64';
                const $comment = $commentTemplate.clone();

                $comment.attr('id', 'comment-' + data['comment_id']);
                $comment.find('.image').attr('src', src);
                $comment.find('.date').text(data['created_formatted']);
                $comment.find('.name').text(data['author_name']);
                $comment.find('.content').text(data['content']);

                if (data['reply'] !== null) {
                    $comment.addClass('reply');
                    const $replyLink = $comment.find('.reply-to-comment');
                    $replyLink.attr('href', '#comment-' + data['reply'])
                    $replyLink.text($('#comment-' + data['reply']).find('.name').text());

                    $replyLink.on('click', function() {
                        const $highlightComment = $(this.hash);
                        $highlightComment.addClass("highlight");
                        setTimeout(function () {
                            $highlightComment.removeClass("highlight");
                        }, 2000);
                    });
                }

                $comment.show();

                return $comment;
            }

            const loadCommentsBatch = function () {
                if (!comments.length) {
                    $commentsSection.text("Zatím tu nejsou žádné komentáře. Buďte první, kdo se podělí o svůj názor!");
                    return false;
                }

                for (let i = 0; i < 10; i++) {
                    if (!comments.length)
                        return false;

                    const comment = comments.shift();
                    const $comment = commentElFromTemplate(comment);

                    $comment.appendTo($commentsSection);

                    comment.children.forEach(reply => {
                        const $reply = commentElFromTemplate(reply);
                        $reply.appendTo($commentsSection);
                    });
                }

                return true;
            };

            loadCommentsBatch();
        });
    }
});