(function (global, $) {
    "use strict";

    $(function () {
       var imageRow = $('.image-overview');
       imageRow.on('click', '.remove-image', function (e) {
           e.preventDefault();

           var imageName = this.dataset.imagename;
           var answer = global.confirm('Skutečně si přejete odstranit obrázek: ' + imageName + '?');
            if (answer) {
                $.nette.ajax({
                    method: 'GET',
                    url: this.dataset.removeurl
                });
            }
       });

    });

})(window, window.jQuery);