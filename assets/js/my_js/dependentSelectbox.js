/**
 * Created by Aleš on 10.03.2016.
 */

(function ($) { // todo
    "use strict";

    $(function () {
        var independentSelect = $('#independent-select');

        independentSelect.on('change', function () {
            var self = $(this);

            var settings = {
                type: 'GET',
                url: self.data('url'),
                data: {}
            };

            settings['data'][self.data('componentName') + '-value'] = self.val();

            $.nette.ajax(settings);
        });

    });

})(window.jQuery);