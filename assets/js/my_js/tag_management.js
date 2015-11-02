(function (global, $) {
    "use strict";

    $(function () {

        var tagsOverview = $('.tags-overview');
        tagsOverview.on('click', '.remove', function (e) {
            e.preventDefault();
            
            var answer = global.confirm('Skutečně si přejete Tag odstranit?');
            if (answer == true) {
                var ajaxSettings = {
                    method: 'GET',
                    url: this.href,
                    success: function (payload) {
                        //console.log(payload);
                        if (payload.errorEl == undefined) { // errorEl is set in TagControl
                            // we does not redraw whole tag overview, there is just one item (the one that is being removed)
                            for (var el in payload.snippets) {
                                var el = $('#' + el);
                                el.fadeOut(250);
                            }
                        }
                    }
                };

                $.nette.ajax(ajaxSettings);
            }
        });

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


        //var tagsOverview = $('.tags-overview');


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

})(window, window.jQuery);