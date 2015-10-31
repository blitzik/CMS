(function ($) {
    "use strict";

    $(function () {

        // CHARACTER COUNTERS
        function processCounter(input, span) {
            var charCount = input.value.length;
            var totalCount = parseInt(input.dataset.textLength);
            if (charCount > 0) {
                span.html('<small>Zbývá <b>' + (totalCount - charCount) + '</b> znaků</small>');
            } else {
                span.html(null);
            }
        }

        var articleForm = $('#article-form');
        articleForm.on('input', '#form-article-title', function () {
            var textCounter = $('#article-title-counter');
            processCounter(this, textCounter);
        });

        articleForm.on('input', '#form-article-intro', function () {
            var textCounter = $('#article-intro-counter');
            processCounter(this, textCounter);
        });


        // TAGS

        var newArticleSection = $('.new-article');

        function setDefaultAppearance(tag)
        {
            tag.style['background-color'] = '#fff';
            tag.style.border = '1px solid #ccc';
            tag.style.color = '#333';
        }

        function highlightTag(tag)
        {
            tag.style['background-color'] = tag.dataset.tagColor;
            tag.style['color'] = '#fff';
            tag.style['border'] = '0';
        }

        var tags = $('.tags input');
        tags.each(function () {
            var span = this.parentNode;
            if (!this.checked) {
                setDefaultAppearance(span);
            }

            span.onmouseover = function () {
                if (!this.lastElementChild.checked) {
                    this.style['background-color'] = '#f0f0f0';
                }
            };

            span.onmouseout = function () {
                if (!this.lastElementChild.checked) {
                    this.style['background-color'] = '#fff';
                }
            };
        });

        newArticleSection.on('click', '.tag-checkbox', function () {
            if (this.checked) {
                highlightTag(this.parentNode);
            } else {
                setDefaultAppearance(this.parentNode);
            }
        });
    });

})(window.jQuery);