/**
 * Main script for wp-admin 
 * @param {window.jQuery} $
 */
(function ($) {
    $(document).ready(function () {
        // Add button editor in WP admin
        var url = 'post.php?post_id=' + jQuery('#post_ID').val() + '&post_type=page&qoob=true';
        var button = '<div class="cube-button-block"><div class="cube-button"><a href="' + url + '"><i class="cube"></i><span>qoob</span></a></div></div>';
        $(button).insertAfter('div#titlediv');
    });
})(window.jQuery);