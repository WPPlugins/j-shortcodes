/*
    Jay Shortcodes
    Collection of userful shortcodes for any Wordpress Theme, Blog or Website
    http://www.jshortcodes.com
*/

//===========================================================================
jQuery(document).ready(function()
{
    // Simple version:
    // jQuery(".jaccordion").accordion({ collapsible: true, active:false });

    // Detect which pane user wants to start active with.
    jQuery(".jaccordion").each (function(index)
        {
        active_pane = jQuery(this).attr("active_pane");
        if (active_pane == undefined)
            {
            active_pane = false;
            }
        else
            {
            active_pane = parseInt (active_pane);
            }

        jQuery(this).accordion({ collapsible: true, autoHeight: false, active: active_pane });
        });



    jQuery(".jtabs" ).tabs();

});
//===========================================================================
