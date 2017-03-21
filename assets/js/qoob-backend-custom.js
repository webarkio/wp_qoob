/**
 * Main script for wp-admin 
 * @param {window.jQuery} $
 */
(function ($) {
    $(document).ready(function () {
        // Add button editor in WP admin
        var postId = jQuery('#post_ID').val();
        var url = 'post.php?post=' + postId + '&action=qoob';
        var button = '<div class="cube-button-block"><div class="cube-button"><a href="' + url + '"><i class="cube"></i><span>' + qoob_backend_custom.button_text + '</span></a></div></div>';
        $(button).insertAfter('div#titlediv');
    });
})(window.jQuery);