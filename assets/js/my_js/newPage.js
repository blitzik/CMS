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

        var articleForm = $('#page-form');
        articleForm.on('input', '#form-page-title', function () {
            var textCounter = $('#page-title-counter');
            processCounter(this, textCounter);
        });

        articleForm.on('input', '#form-page-intro', function () {
            var textCounter = $('#page-intro-counter');
            processCounter(this, textCounter);
        });


        // TAGS

        var tagsPickingBox = $('.tags-picking-box');

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

        var tags = $('.article-tags-list input');
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

        tagsPickingBox.on('click', '.tag-checkbox', function () {
            if (this.checked) {
                highlightTag(this.parentNode);
            } else {
                setDefaultAppearance(this.parentNode);
            }
        });
    });

})(window.jQuery);