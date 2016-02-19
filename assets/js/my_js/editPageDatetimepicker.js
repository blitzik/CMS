(function ($) {
    "use strict";

    $(function () {
        $('#datetimepicker').datetimepicker({
            mask: false,
            format: 'j.n.Y H:i',
            lang: 'cs',
            minDate: new Date()
        });
    });

})(window.jQuery);