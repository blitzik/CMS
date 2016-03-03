/**
 * Created by Aleš on 02.03.2016.
 */

(function (global) {
    "use strict";

    function setCaretPosition(elemId, caretPos) {
        var elem = document.getElementById(elemId);

        if(elem != null) {
            if(elem.createTextRange) {
                var range = elem.createTextRange();
                range.move('character', caretPos);
                range.select();
            }
            else {
                if(elem.selectionStart) {
                    elem.focus();
                    elem.setSelectionRange(caretPos, caretPos);
                }
                else
                    elem.focus();
            }
        }
    }


    var commentTextarea = document.getElementById('comment-textarea');

    var reactionButtons = document.getElementsByClassName('reaction-button');

    for (var i = 0; i < reactionButtons.length; i++) {
        reactionButtons[i].addEventListener('click', function (e) {
            e.preventDefault();

            var l = commentTextarea.value.length;
            commentTextarea.value += (l > 0 ? '\r\n' : '') + '@' + this.dataset.reply + ' ';

            global.location.hash = 'new-comment-form';
            setCaretPosition('comment-textarea', commentTextarea.value.length);
        });
    }

})(window);