/**
 * Main script for wp-admin 
 * @param {window.jQuery} $
 */
(function($) {
    $(document).ready(function() {
        if (typeof(typenow) !== 'undefined' && typenow === 'page') {
            // Add button editor in WP admin
            var postId = jQuery('#post_ID').val();
            var url = 'post.php?post=' + postId + '&action=qoob';
            var button = '<div class="cube-button-block"><div class="cube-button"><a href="' + url + '"><i class="cube"></i><span>' + qoob_backend_custom.button_text + '</span></a></div></div>';
            $(button).insertAfter('div#titlediv');
        }

        // page "qoob-manage-libs"
        var form = $('#qoob-filters'),
            filters = form.find('.qoob-filters');
        filters.hide();
        form.find('input:radio').change(function() {
            filters.slideUp('fast');
            switch ($(this).val()) {
                case 'url':
                    $('#qoob-url').slideDown();
                    break;
                case 'file':
                    $('#qoob-file').slideDown();
                    break;
            }
        });
        $('#qoob-url').slideDown();
    });
})(window.jQuery);
