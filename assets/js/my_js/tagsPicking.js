(function ($) {
    "use strict";

    $(function () {

        var publishCheckbox = $('#is-published-checkbox');


        // TAGS

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

        var newArticleSection = $('.new-article');
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
            console.log(this);
            if (this.checked) {
                highlightTag(this.parentNode);
            } else {
                setDefaultAppearance(this.parentNode);
            }
        });
    });

})(window.jQuery);