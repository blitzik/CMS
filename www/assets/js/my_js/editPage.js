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

        var titleSpan = $('#page-title-counter');
        var introSpan = $('#page-intro-counter');
        var textSpan = $('#page-text-counter');

        articleForm.on('input', '#form-page-title', {span: titleSpan}, function (event) {
            processCounter(this, event.data.span);
        });

        articleForm.on('input', '#form-page-intro', {span: introSpan}, function (event) {
            processCounter(this, event.data.span);
        });

        articleForm.on('input', '#form-page-text', {span: textSpan}, function (event) {
            var charCount = this.value.length;
            event.data.span.html('<small>Napsáno <b>' + charCount + '</b> znaků</small>');
        });

        var textAreas = $('.page-textArea');
        autosize(textAreas);

    });

})(window.jQuery);