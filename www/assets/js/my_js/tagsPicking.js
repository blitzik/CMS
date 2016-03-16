/**
 * Created by Aleš on 26.02.2016.
 */

(function ($) {
    "use strict";

    $(function () {

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
            tag.style['border'] = '1px solid ' + tag.dataset.tagColor;
        }

        var tags = $('.tags-list input');
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