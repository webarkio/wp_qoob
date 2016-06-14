/**
 * Control edit draft page and qoob
 * 
 * @param {window.jQuery} $
 */
(function ($) {
    $('html').removeClass('wp-toolbar');
    $('#wpadminbar').hide();

    $(document).ready(function () {
        $('body').removeClass('admin-bar').addClass('qoob-editor');

        $('#wpadminbar').hide();
        $('.edit-link').hide();
        $('#screen-meta-links, #screen-meta').hide();
    });
})(window.jQuery);