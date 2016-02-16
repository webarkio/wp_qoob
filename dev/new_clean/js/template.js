jQuery.expr[':'].Contains = function(a, i, m) {
    return jQuery(a).text().toUpperCase().indexOf(m[3].toUpperCase()) >= 0;
};

$(document).ready(function() {
    
    jQuery('.search_bar').on('keyup','input',function() {  
        tree_search(this);
    });

    highlight();

    jQuery('#source-view').on('shown', function () {
        highlight();
    });
    
    jQuery('.extend_parents .name_parent').each(function(){
        var textold = jQuery(this).text();
        var new_text = textold.substring(1);
        jQuery(this).text(new_text);
    });

});
// work with highkight plugin
function highlight(){
    jQuery('pre code').each(function(i, e) {
        var code = $(this);
        code.addClass('php');
        hljs.highlightBlock(e);
        height = code.outerHeight();
        code.parent().css('height',height + 'px');
    });
}
function tree_search(input) {
    treeview = jQuery(input).parents('.accordion-group').find('.accordion-inner');

    // make all items visible again
    treeview.find('li:hidden').show();

    // hide all items that do not match the given search criteria
    if (jQuery(input).val()) {
        treeview.find('li').not(':has(a:Contains(' + jQuery(input).val() + '))').hide();
    }
}