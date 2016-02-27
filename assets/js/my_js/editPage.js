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

    });

})(window.jQuery);