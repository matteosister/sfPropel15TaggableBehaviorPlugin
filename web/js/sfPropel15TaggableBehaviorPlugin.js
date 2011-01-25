/* 
 *  Matteo Giachino <matteog@gmail.com>
 *  Just for fun...
 */

jQuery(document).ready( function() {
    jQuery('ul#sfPropel15TaggablePlugin li input').each( function() {
        jQuery(this).change(reloadTagsLayout);
    });
});

function reloadTagsLayout()
{
    jQuery('ul#sfPropel15TaggablePlugin li input').each( function() {
        if (jQuery(this).attr('checked')) {
            jQuery(this).siblings('label').css('text-decoration', 'line-through');
        } else {
            jQuery(this).siblings('label').css('text-decoration', 'none');
        }
    });
}