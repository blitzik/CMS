(function ($) {
    "use strict";

    $(function () {

        function setDefaultAppearance(tag)
        {
            tag.style['background-color'] = 'transparent';
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
            setDefaultAppearance(this.parentNode);
        });

        tags.on('click', function () {
            if (this.checked) {
                highlightTag(this.parentNode);
            } else {
                setDefaultAppearance(this.parentNode);
            }
        });
    });

})(window.jQuery);