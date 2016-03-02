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
        
        // menu stop click
        $('.navbar .menu-item a').click(function(e){
            e.preventDefault();
        });
    });
})(window.jQuery);