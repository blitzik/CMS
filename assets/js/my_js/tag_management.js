(function ($) {
    "use strict";

    $(function () {

        function markInputAsWrong(input)
        {
            input.style['border'] = '1px solid red';
            input.style['background-color'] = '#ffe3e3';
        }

        function markInputAsOk(input)
        {
            input.style['border'] = '1px solid #ccc';
            input.style['background-color'] = '#fff';
        }

        function checkColorFormat(color)
        {
            var colorRegExp = /^#(([0-f]{3})|[0-f]{6})$/;
            if (colorRegExp.test(color)) {
                return true;
            } else {
                return false;
            }
        }

        function processColor(input, submitButton)
        {
            if (!checkColorFormat(input.value)) {
                markInputAsWrong(input);
                submitButton.attr('disabled', true);
            } else {
                markInputAsOk(input);
                submitButton.attr('disabled', false);
            }
        }


        var tagsOverview = $('.tags-overview');


        // OVERVIEW
        tagsOverview.on('input', '.tag-input', function () {
            var tagId = this.dataset.tagid;
            var tagToChange = $('#tag-' + tagId);

            var submitButton = $('#tag-submit-' + tagId);
            processColor(this, submitButton);

            tagToChange.css('background-color', this.value);

            var tagUndoButton = $('#tag-undo-button-' + tagId);
            if (this.value != tagUndoButton.data('tagOriginalColor')) {
                tagUndoButton.css('display', 'block');
                tagUndoButton.attr('disabled', false);
            }
        });


        // CREATION FORM
        var inputColor = $('#creation-form-color');
        inputColor.on('input', function () {
            var submitButton = $('#new-tag-submit');
            processColor(this, submitButton);

            var colorBox = $('#color-box');
            if (this.value != '') {
                if (checkColorFormat(this.value)) {
                    colorBox.css('background-color', this.value);
                } else {
                    colorBox.css('background-color', 'transparent');
                }
            } else {
                markInputAsOk(this);
            }
        });

        // UNDO BUTTONS
        var undoColorButtons = $('.undo-color');
        //undoColorButtons.css('display', 'block');
        undoColorButtons.attr('disabled', true);

        tagsOverview.on('click', '.undo-color', function (event) {
            event.preventDefault();

            var inputColor = $('#tag-color-input-' + this.dataset.tagid);
            inputColor.val(this.dataset.tagOriginalColor);

            var submitButton = $('#tag-submit-' + this.dataset.tagid);
            processColor(inputColor.get(0), submitButton);

            var tagToChange = $('#tag-' + this.dataset.tagid);
            tagToChange.css('background-color', this.dataset.tagOriginalColor);

            this.setAttribute('disabled', 'disabled');
            this.style.display = 'none';
        });

    });

})(window.jQuery);