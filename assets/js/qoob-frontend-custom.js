/**
 * Control iframe page
 * 
 * @param {window.jQuery} $
 */
(function ($) {
    $('html').removeClass('no-js').addClass('active-js');
    $('body').removeClass('admin-bar');

    $(document).ready(function () {
        $('#wpadminbar').hide();
    });
})(window.jQuery);