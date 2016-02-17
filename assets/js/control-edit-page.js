/**
 * Control edit draft page and builder
 * 
 * @param {window.jQuery} $
 */
(function ($) {
    $('html').removeClass('wp-toolbar');
    $('#wpadminbar').hide();

    $(document).ready(function () {
        $('body').removeClass('admin-bar').addClass('builder-editor');

        $('#wpadminbar').hide();
        $('.edit-link').hide();
        $('#screen-meta-links, #screen-meta').hide();
    });
})(window.jQuery);