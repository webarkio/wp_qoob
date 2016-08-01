jQuery(function($) {
    $('.has_children > a').removeAttr('href');

    $('.menu_list > li > a').click(function(){
        if ( $(this).parent().hasClass('active') ){
            $(this).parent().find('ul').hide(500).removeClass('active');
            $(this).parent().removeClass('active');
        } else {
            $('.menu_list li ul').slideUp();
            $(this).next().slideToggle();
            $('.menu_list li').removeClass('active');
            $(this).parent().addClass('active');
        }
    });
});