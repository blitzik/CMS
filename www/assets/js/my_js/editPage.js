(function ($) {
    "use strict";

    $(function () {

        // CHARACTER COUNTERS

        function processCounter(input, span) {
            var charCount = input.value.length;
            var totalCount = parseInt(input.dataset.textLength);
            var text = span.data('text');
            if (charCount > 0) {
                text = text.replace('#', '<b>' + (totalCount - charCount) + '</b>');
                span.html('<small>' + text + '</small>');
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
            var text = event.data.span.data('text');
            console.log(text);
            text = text.replace('#', '<b>' + charCount + '</b>');
            event.data.span.html('<small>' + text + '</small>');
        });

        var textAreas = $('.page-textArea');
        autosize(textAreas);

    });

})(window.jQuery);