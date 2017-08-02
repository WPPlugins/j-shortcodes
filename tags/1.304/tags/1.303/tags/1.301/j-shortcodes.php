<?php
/*
Plugin Name: J Shortcodes
Plugin URI: http://www.jshortcodes.com/
Version: 1.301
Author: Gleb Esman, http://www.jshortcodes.com/
Author URI: http://www.jshortcodes.com/
Description: Collection of useful shortcodes to create custom column layouts, add custom buttons, content boxes, feature and call to action boxes. Pick any color or size for any element. Create sophisticated column layouts directly within any page, post or even sidebar widget. Check out <a href="http://www.jshortcodes.com/shortcodes/">J Shortcodes samples, demos and tutorials</a>.
*/

define('J_SHORTCODES_VERSION',  '1.301');

include (dirname(__FILE__) . '/j-include-all.php');

//---------------------------------------------------------------------------
// Plugins actions, hooks and filters
register_activation_hook   (__FILE__,                    'JAY__activated');

add_action                 ('init',                      'JAY__init',                  10);
add_action                 ('wp_head',                   'JAY__wp_head',               10);
add_action                 ('wp_head',                   'JAY__wp_head_custom_css',    999);    // Make it last
add_action                 ('admin_init',                'JAY__admin_init');
add_action                 ('admin_head',                'JAY__admin_head');
add_action                 ('admin_menu',                'JAY__admin_menu');

add_filter                 ('widget_text',               'do_shortcode');
add_filter                 ('plugin_row_meta',           'JAY__set_plugin_meta', 10, 2);
//---------------------------------------------------------------------------

//---------------------------------------------------------------------------
// Shortcodes
// [jbutton]
add_shortcode              ('jbuttonify',                'JAY__shortcode__jbuttonify');   // Experimental. Converts words on page to randomly colored and sized buttons.

add_shortcode              ('jbox',                      'JAY__shortcode__jbox');
add_shortcode              ('jbutton',                   'JAY__shortcode__jbutton');
add_shortcode              ('jcolumns',                  'JAY__shortcode__jcolumns');
add_shortcode              ('j-memberwing',              'JAY__shortcode__jmemberwing');
add_shortcode              ('jpage',                     'JAY__shortcode__jpage');
add_shortcode              ('jfeed',                     'JAY__shortcode__jfeed');
add_shortcode              ('jtabs',                     'JAY__shortcode__jtabs');
add_shortcode              ('jaccordion',                'JAY__shortcode__jaccordion');
//---------------------------------------------------------------------------

//---------------------------------------------------------------------------
// Globals
$g_theme_unavail_message = '<div align="center" style="border:1px solid red;margin:3px;padding:3px;font-size:11px;line-height:13px;background-color:#ffd;">Warning: "{THEME}" theme must be enabled via J-Shortcodes settings panel:<br />Admin&nbsp;-&gt;&nbsp;J-Shortcodes&nbsp;-&gt;&nbsp;General Settings, before it could be used.</div>';
//---------------------------------------------------------------------------

//===========================================================================
// Initial activation code here such as: DB tables creation, storing initial settings.

function JAY__activated ()
{
   global   $g_JAY__config_defaults;
   // Initial set/update default options

   $jay_default_options = $g_JAY__config_defaults;

   // This will overwrite default options with already existing options but leave new options (in case of upgrading to new version) untouched.
   $jay_settings = JAY__get_settings ();
   if (is_array ($jay_settings))
      {
      foreach ($jay_settings as $key=>$value)
         $jay_default_options[$key] = $value;
      }

   //------------------------------------------------------------------------
   // Renamed/modified Settings migration
   // ...
   //------------------------------------------------------------------------

   // Repopulating DB with new meta
   update_option ('J-Shortcodes', $jay_default_options);
}
//===========================================================================

//===========================================================================
function JAY__set_plugin_meta ($links, $file)
{
   $plugin = plugin_basename(__FILE__);

   // create link
   if ($file == $plugin)
      {
      return
         array_merge (
            $links,
            array( sprintf( '<div><a style="border:1px solid #888;padding:1px 4px;background-color:#ffc;-moz-border-radius:7px; -webkit-border-radius: 7px; -khtml-border-radius: 7px; border-radius: 7px;" href="options-general.php?page=j-shortcodes-settings">%s</a></div>', __('Settings')))
            );
      }

   return $links;
}
//===========================================================================

//===========================================================================
function JAY__Load_Jquery ($is_admin)
{
   $jquery_version      = "1.4.4";
   $jquery_ui_version   = "1.8.9";

   //---------------------------------------
   // Make sure jQuery is properly loaded.
   wp_deregister_script ('jquery');             // using wp_deregister_script() to disable the version that comes packaged with WordPress
   wp_deregister_script ('jquery-ui-core');
   wp_deregister_script ('jquery-ui-tabs');

   wp_register_script   ('jquery',           "http://ajax.googleapis.com/ajax/libs/jquery/{$jquery_version}/jquery.min.js");         // using wp_register_script() to register updated libraries (this example uses the CDN from Google but you can use any other CDN or host the scripts yourself)
   wp_register_script   ('jquery-ui-core',   "http://ajax.googleapis.com/ajax/libs/jqueryui/{$jquery_ui_version}/jquery-ui.min.js");

   wp_enqueue_script    ('jquery');          // using wp_enqueue_script() to load the updated libraries
   wp_enqueue_script    ('jquery-ui-core');
   wp_enqueue_script    ('jquery-ui-tabs');
   wp_enqueue_script    ('jquery-ui-accordion');
   //---------------------------------------
}
//===========================================================================

//===========================================================================
function JAY__Get_Extra_Header_HTML ($is_admin)
{
   global $g_JAY__plugin_directory_url;
   $jay_settings = JAY__get_settings();

   $jquery_ui_css_version   = "1.8.9";

   // Force load stylesheet and .js
   $extra_html =<<<TTT
<link rel="stylesheet" type="text/css" href="{$g_JAY__plugin_directory_url}/css/jay.css" />
<script type="text/javascript" src="{$g_JAY__plugin_directory_url}/js/jay.js"></script>
TTT;

   foreach ($jay_settings['jquery_themes'] as $theme_name => $val)
      {
      if ($val || $is_admin || $theme_name == 'smoothness')
         $extra_html .=<<<TTT
<link rel="stylesheet" type="text/css" href="{$g_JAY__plugin_directory_url}/css/jquery/{$theme_name}/jquery-ui-1.8.9.custom.css" />
TTT;
      }

   return $extra_html;
}
//===========================================================================

//===========================================================================
function JAY__init ()
{

   // Make sure jQuery is properly loaded.
   JAY__Load_Jquery (FALSE);

   $jay_settings = JAY__get_settings();

   if (@$jay_settings['disable-wpautop'])
      {
      remove_filter              ('the_content',               'wpautop');
      remove_filter              ('the_excerpt',               'wpautop');
      }
}
//===========================================================================

//===========================================================================
function JAY__wp_head ()
{
   echo JAY__Get_Extra_Header_HTML (FALSE);
}
//===========================================================================

//===========================================================================
function JAY__wp_head_custom_css ()
{
   global   $g_JAY__config_defaults;

   $jay_settings = JAY__get_settings ();
   if ($jay_settings['custom_css'] && $jay_settings['custom_css'] != $g_JAY__config_defaults['custom_css'])
      {
?>
<style type="text/css">
<!--
/* J shortcodes custom CSS code. http://www.jshortcodes.com */
<?php echo $jay_settings['custom_css']; ?>

-->
</style>

<?php
      }
}
//===========================================================================

//===========================================================================
function JAY__admin_init ()
{
   // Make sure jQuery is properly loaded.
   JAY__Load_Jquery (TRUE);
}
//===========================================================================

//===========================================================================
function JAY__admin_head ()
{
   echo JAY__Get_Extra_Header_HTML (TRUE);
}
//===========================================================================

//===========================================================================
function JAY__admin_menu ()
{
   global $g_JAY__plugin_directory_url;

   add_menu_page    (
      'J-Shortcodes General Settings',           // Page Title
      '<b>J</b>-Shortcodes',              // Menu Title - lower corner of admin menu
      'administrator',                          // Capability
      'j-shortcodes-settings',                  // handle
      'JAY__render_general_settings_page',      // Function
      $g_JAY__plugin_directory_url . '/J_icon_16x.png'         // Icon URL
      );

   add_submenu_page (
      'j-shortcodes-settings',                  // Parent
      'J-Shortcodes General Settings',           // Page Title
      '<span style="font-weight:bold;color:#087bf9;">&bull;</span>&nbsp;General Settings',                       // Menu Title
      'administrator',                          // Capability
      'j-shortcodes-settings',                  // Handle - First submenu's handle must be equal to parent's handle to avoid duplicate menu entry.
      'JAY__render_general_settings_page'       // Function
      );
}
//===========================================================================

//===========================================================================
function JAY__render_general_settings_page ()         { JAY__render_settings_page   ('general'); }
//===========================================================================

//===========================================================================
// Do admin panel business, assemble and output admin page HTML
function JAY__render_settings_page ($menu_page_name)
{
   if (isset ($_POST['button_update_jay_settings']))
      {
      JAY__update_settings ();
      echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:110%;border: 1px solid gray;margin:5px;">
Settings updated!
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_jay_settings']))
      {
      JAY__reset_all_settings ();
      echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:110%;border: 1px solid gray;margin:5px;">
All settings reverted to all defaults
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_partial_jay_settings']))
      {
      JAY__reset_partial_settings ();
      echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:110%;border: 1px solid gray;margin:5px;">
Settings on this page reverted to defaults
</div>
HHHH;
      }
   else if (isset($_POST['button_subscribe_to_js_notifications']))
      {
      JAY__SubscribeToAweber ($_POST['subscribe_email']);
      echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:110%;border: 1px solid gray;margin:5px;">
Thank you for subscribing to J-Shortcodes notifications. Confirmation email will be sent to <b>{$_POST['subscribe_email']}</b> shortly.
<br />Make sure to click on confirmation link to activate your subscription.
<br />If you will not receive an email within a few minutes - please check your spam folder.
</div>
HHHH;
      }


   // Output full admin settings HTML
   JAY__render_admin_page_html ($menu_page_name);
}
//===========================================================================

//===========================================================================
function JAY__shortcode__jbuttonify ($atts, $content="")
{
   $colors           = array ('white', 'gray', 'darkgray', 'black', 'orange', 'red', 'green', 'blue', 'rosy', 'pink');
   $colors_idx_max   = count ($colors) - 1;
   $sizes            = array ('xsmall', 'small', 'medium', 'large', 'xlarge', 'xxlarge');
   $sizes            = array ('xsmall', 'small', 'medium', 'large');
   $sizes_idx_max    = count ($sizes) - 1;
   $roundness        = array ('yes', 'no');

   $words      = preg_split ("@[\s\n\r]+@s", strip_tags($content));

   $output_html = "";
   foreach ($words as $word)
      {
      if (trim($word))
         {
         $color_idx   = rand (0, $colors_idx_max);
         $size_idx    = rand (0, $sizes_idx_max);
         $rounded_idx = rand (0, 1);

         $output_html .= "[jbutton size='{$sizes[$size_idx]}' color ='{$colors[$color_idx]}' rounded='{$roundness[$rounded_idx]}']{$word}[/jbutton] ";
         }
      else
         $output_html .= $word;
      }

   return JAY__do_shortcode ($output_html);
}
//===========================================================================

//===========================================================================
// [jbutton] Shortcode:
// --------------------
//    [jbutton
//       size="xsmall|small|*medium|large|xlarge|xxlarge"
//       color="*white|gray|darkgray|black|orange|red|green|blue|rosy|pink"
//       rounded="yes|*no"
//       icon="*|yes|no|info|download|question|globe|add|doc|forum|pdf|love|http://link.to/my/icon.png"
//       link="*#|http://jump.on/click"
//       newpage="yes|*no"
//       a_css="*"
//       span_css="*"
//       ]
//       Button Text
//    [/jbutton]
//
// TIPS:
// -----
//    key="value1|value2"     - means key is mandatory and possible values for the key are 'value1' or 'value2'
//    key="value1|*value2"    - means if key is not specified, the default value used is 'value2'
//
// NOTES:
// ------
// -  For button size:
//    'xsmall'    - recommended icon size: 10x10px
//    'small'     - recommended icon size: 14x14px
//    'medium'    - recommended icon size: 16x16px
//    'large'     - recommended icon size: 20x20px
//    'xlarge'    - recommended icon size: 28x28px
//    'xxlarge'   - recommended icon size: 36x36px

function JAY__shortcode__jbutton ($atts, $content="")
{
   global $g_JAY__plugin_directory_url;

   extract (shortcode_atts (
      array(
         'size'         => 'medium',
         'color'        => 'white',
         'rounded'      => 'no',
         'icon'         => '',
         'link'         => '#',
         'newpage'      => 'no',       // if 'yes' - adds: target="_blank" to <a> tag.
         'border'       => '',         // thickness of border in pixels
         'a_css'        => '',         // Extra custom CSS styles for <a> tag. Ex:    "width:100px;font-weight:bold;"
         'span_css'     => '',         // Extra custom CSS styles for <span> tag. Ex: "width:100px;font-weight:bold;"
         ),
         $atts));

   if (strpos ($icon, '/') !== FALSE)
      $icon_is_url = TRUE;
   else
      $icon_is_url = FALSE;

   $a_padding_num    = "";
   $span_css_style   = "";
   $is_icon          = "";

   //------------------------------------------
   $a_css_rules = array();
   if ($border)
      {
      $border_num = rtrim ($border, 'px;');
      $a_css_rules[] = "border-width:{$border_num}px;";
      }
   // This must be processed last.
   if ($a_css)
      {
      $a_css_custom = rtrim ($a_css, ';');
      $a_css_rules[] = $a_css_custom;
      }
   if (count($a_css_rules))
      {
      $a_css_extra = implode ($a_css_rules);
      $a_css_extra = "style=\"{$a_css_extra}\"";
      }
   else
      $a_css_extra = "";
   //------------------------------------------

   if ($newpage == 'yes' || $newpage==='1')
      $is_newpage = 'target="_blank"';
   else
      $is_newpage = '';

   if ($icon)
      {
      // icon="..." was specified
      $is_icon = "iconized";

      // Calculate padding
      switch ($size)
         {
         case 'xsmall'  :  $icon_size_prefix = "10x10"; break;
         case 'small'   :  $icon_size_prefix = "14x14"; break;
         case 'large'   :  $icon_size_prefix = "20x20"; break;
         case 'xlarge'  :  $icon_size_prefix = "28x28"; break;
         case 'xxlarge' :  $icon_size_prefix = "36x36"; break;

         case 'medium'  :
         default        :  $icon_size_prefix = "16x16"; break;
         }

      if ($icon_is_url)
         // Icon URL specified
         $icon_url = $icon;
      else
         $icon_url = $g_JAY__plugin_directory_url . "/images/{$icon_size_prefix}-{$icon}.png";

      $span_css_style = "style=\"background:url({$icon_url}) no-repeat 0 45%;{$span_css}\"";
      }


   if ($rounded == 'yes' || $rounded === '1')
      $is_rounded = 'rounded';
   else
      $is_rounded = '';

   $final_html = "<a {$a_css_extra} {$is_newpage} class=\"jbutton {$color} {$size} {$is_rounded} {$is_icon}\" href=\"{$link}\"><span {$span_css_style}>{$content}</span></a>";

   return JAY__do_shortcode ($final_html);
}
//===========================================================================

//===========================================================================
//    [jbox
//       width="*"
//       color="white|*gray|platinum|red|green|blue|yellow"
//       icon="*|http://link.to/icon.png"
//       title="*"
//       border="*1"
//       radius="*18"
//       shadow="*2"
//       jbox_css="*"
//       icon_css="*"
//       title_css="*"
//       content_css="*"
//       vgradient="*"
//    ] Jbox content text/html [/jbox]
//

function JAY__shortcode__jbox  ($atts, $content="")
{
   extract (shortcode_atts (
      array(
         'width'        => '',      // In pixels.
         'color'        => 'gray',
         'icon'         => '',
         'title'        => '',
         'border'       => '',      // thickness of border in pixels
         'radius'       => '',      // Default border radius = 18px.
         'shadow'       => '',      // Relative size of shadow in approx pixels
         'jbox_css'     => '',      // custom css code for outer box <div>. Ex: "background-color:#FFA;"
         'icon_css'     => '',      // custom css code for box. Ex: "background-color:#FFA;"
         'title_css'    => '',      // custom css code for box. Ex: "background-color:#FFA;"
         'content_css'  => '',      // custom css code for box. Ex: "background-color:#FFA;"
         'vgradient'    => '',      // Top to Bottom gradient, CSS colors definitions (including '#' if needed) separated by '|'. Ex: "#4f165a|#92764e"

         'link'         => '#',     // NOT SUPPORTED YET
         ),
         $atts));

   $jbox_css_full    = '';
   $icon_css_full    = '';
   $title_css_full   = '';
   $content_css_full = '';

   $jbox_css_rules = array();
   if ($border)
      $jbox_css_rules[] = "border-width:" . rtrim($border, "px;") . "px;";
   if ($radius)
      {
      $radius_num = rtrim($radius, 'px;');
      $jbox_css_rules[] = "-moz-border-radius: {$radius_num}px;";
      $jbox_css_rules[] = "-webkit-border-radius: {$radius_num}px;";
      $jbox_css_rules[] = "-khtml-border-radius: {$radius_num}px;";
      $jbox_css_rules[] = "border-radius: {$radius_num}px;";
      }
   if ($jbox_css)
      $jbox_css_rules[] = rtrim ($jbox_css, ';') . ';';
   if ($vgradient)
      {
      $colors = explode ('|', $vgradient);
      $colors[0] = rtrim ($colors[0], ';');
      $colors[1] = rtrim ($colors[1], ';');

      $jbox_css_rules[] = "background: -webkit-gradient(linear, left top, left bottom, from({$colors[0]}), to($colors[1]));";
      $jbox_css_rules[] = "background: -moz-linear-gradient(top, {$colors[0]}, {$colors[1]});";
      $jbox_css_rules[] = "filter:  progid:DXImageTransform.Microsoft.gradient(startColorstr='{$colors[0]}', endColorstr='{$colors[1]}');";
      }
   if ($shadow)
      {
      $shadow_num       = rtrim ($shadow, 'px;');
      if ($shadow_num)
         $shadow_blur_num  = $shadow_num+2;
      else
         $shadow_blur_num  = $shadow_num;
      $jbox_css_rules[] = "-webkit-box-shadow: {$shadow_num}px {$shadow_num}px {$shadow_blur_num}px rgba(0,0,0,.15);";
      $jbox_css_rules[] = "-moz-box-shadow: {$shadow_num}px {$shadow_num}px {$shadow_blur_num}px rgba(0,0,0,.15);";
      $jbox_css_rules[] = "box-shadow: {$shadow_num}px {$shadow_num}px {$shadow_blur_num}px rgba(0,0,0,.15);";
      }

   $icon_html = "";
   if ($icon)
      {
      if ($icon_css)
         $icon_css_full = 'style="' . rtrim($icon_css, ';') . ';"';

      $icon_html =<<<TTT
  <div {$icon_css_full} class="jbox-icon {$color}">
    <img src="{$icon}">
  </div>
TTT;
      }

   $title_html = "";
   if ($title)
      {
      if ($title_css)
         $title_css_full = 'style="' . rtrim($title_css, ';') . ';"';

      $title_html =<<<TTT
  <div {$title_css_full} class="jbox-title {$color}">{$title}</div>
TTT;
      }

   $content = JAY__trim_br ($content);

   if ($content_css)
      $content_css_full = 'style="' . rtrim($content_css, ';') . ';"';

   if (count($jbox_css_rules))
      $jbox_css_full = 'style="' . implode ($jbox_css_rules) . '"';
   else
      $jbox_css_full = '';
   $box_html = "<div class=\"jbox {$color}\" {$jbox_css_full}>{$icon_html}{$title_html}<div {$content_css_full} class=\"jbox-content\">{$content}</div></div>";

   return JAY__do_shortcode ($box_html);
}
//===========================================================================

//===========================================================================
// [jcolumns] shortcode.
/*
   [jcolumns
      model="*|M,N,P,Q..."
      halign="*left|center|right"
      valign="*top|middle|bottom"
      colclass="*"
      colgap="*12"
      colcss="*"
      stripbr="*yes|no"
      outbordercss="*"
      inbordercss="*"
      topbordercss="*"
      bottombordercss="*"
   ]
     ...column 1 content...
      [jcol/]
     ...column 2 content...
      [jcol/]
     ...column 3 content...
   [/jcolumns]
*/
// Notes:
//    -  model="111" - 3 equal columns 33% each; "1231" - 4 columns, 14%, 28%, 52%, 14%; "1111" - 4 columns 25% each, NOTE: when all columns need to be equal 'model=' param can be omitted.
//    -  '[jcol/]' - is the column separator
//    -  If 'model=' skipped - equal columns will result.
//    -  stripbr="1" - will strip one <br /> from the beginning and the end of each column's content. It is unsolicitly added by Wordpress. This is default.
//    -  stripbr="0" - will leave column content as is.

function JAY__shortcode__jcolumns  ($atts, $content="")
{
   extract (shortcode_atts (
      array(
         'model'        => '',      // comma-delimited relative units: "1,2,1,1". Could use as percentages: "20,40,20,20"
         'halign'       => 'left',
         'valign'       => 'top',   // top|middle|center|bottom
         'stripbr'      => 'yes',
         'colclass'     => '',
         'colgap'       => '12',    // gap between columns in pixels. Note: min:2 pixels
         'colcss'       => '',      // CSS rules to be applied to div of each column.
         'outbordercss' => '',      // Ex: "1px solid gray". Default:none. CSS of the outer left and right border.
         'inbordercss'  => '',      // Ex: "1px solid gray". Default:none. CSS of the border-separator in between columns.
         'topbordercss' => '',      // Ex: "1px solid gray". Default:none. CSS of the top border of columns.
         'bottombordercss' => '',   // Ex: "1px solid gray". Default:none. CSS of the bottom border of columns.
         ),
         $atts));

   if ($colclass)
      $colclass_txt = "class=\"{$colclass}\"";
   else
      $colclass_txt = "";

   $colgap_num       = rtrim ($colgap, "px ;");
   if ($colcss)
      $colcss        = rtrim ($colcss, ';') . ';';
   $colcss_any       = "margin:0;padding:0;" . $colcss;

   if ($topbordercss)
      $topbordercss = "border-top:" . trim ($topbordercss, ';') . ';';

   if ($bottombordercss)
      $bottombordercss = "border-bottom:" . trim ($bottombordercss, ';') . ';';

   if ($valign == 'center')
      $valign = 'middle'; // Make it HTML-valid

   if ($valign == 'middle')
      $valign_css_rule = 'vertical-align:middle;';
   else
      $valign_css_rule = '';

   // Process column separators
   if (!$colgap_num || $colgap_num<2)
      $colgap_num = 2;
   $colgap_num1      = floor($colgap_num/2);       // 3
   $colgap_num2      = $colgap_num - $colgap_num1; // 4, if total 7.

   if ($outbordercss)
      {
      $outbordercss = rtrim ($outbordercss, ';') . ';';
      // To make first and last columns look gapped in the same way as inner columns, we must use inner gap params to calculate width of outside "border" columns.
      $left_gap_html    = "<td width=\"{$colgap_num2}\" style=\"margin:0;padding:0;border-left:{$outbordercss}{$topbordercss}{$bottombordercss}\"></td>";
      $right_gap_html   = "<td width=\"{$colgap_num1}\" style=\"margin:0;padding:0;border-right:{$outbordercss}{$topbordercss}{$bottombordercss}\"></td>";
      }
   else
      {
      $left_gap_html    = "";
      $right_gap_html   = "";
      }

   if ($inbordercss)
      {
      $inbordercss = rtrim ($inbordercss, ';') . ';';
      $inner_gap_html =  "<td width=\"{$colgap_num1}\" style=\"margin:0;padding:0;border-right:{$inbordercss}{$topbordercss}{$bottombordercss}\"></td>";
      $inner_gap_html .= "<td width=\"{$colgap_num2}\" style=\"margin:0;padding:0;{$topbordercss}{$bottombordercss}\"></td>";
      }
   else
      {
      $inner_gap_html =  "<td width=\"{$colgap_num1}\" style=\"margin:0;padding:0;{$topbordercss}{$bottombordercss}\"></td>";
      $inner_gap_html .= "<td width=\"{$colgap_num2}\" style=\"margin:0;padding:0;{$topbordercss}{$bottombordercss}\"></td>";
      }


   // "1,3,5" = 9 units, 3 columns
   $columns_content = explode ('[jcol/]', $content);

   // If 'model=' skipped - equal columns will result.
   if (!$model)
      {
      // 1,1,1,1,1
      $model = rtrim (str_repeat ("1,", count($columns_content)), ',');
      }

   $column_specs = array();
   $total_units  = 0;
   $model_arr = explode (',', $model);
   $chars = count ($model_arr);
   for ($i=0; $i<$chars; $i++)
      {
      $column_specs[] = $model_arr[$i];
      $total_units += $model_arr[$i];
      }

   $total_columns = count($column_specs);
   $pct_per_unit = 100/$total_units;

   $table_columns = "";
   $colcss_current = $colcss_any;   // Used to be different for first/last, now the same.
   for ($i=0; $i<$total_columns; $i++)
      {
      $column_width_pct = floor($column_specs[$i] * $pct_per_unit);
      $column_content   = $columns_content[$i];

      if ($stripbr == 'yes')
         {
         $column_content = JAY__trim_br ($column_content);
         }

      $table_columns .= "<td width=\"{$column_width_pct}%\" align=\"{$halign}\" valign=\"{$valign}\" style=\"{$topbordercss}{$bottombordercss}{$valign_css_rule}\"><div align=\"{$halign}\" {$colclass_txt} style=\"{$colcss_current};\">{$column_content}</div></td>";
      if ($i != ($total_columns-1))
         {
         // Insert "gap" column
         $table_columns .= $inner_gap_html;
         }
      }

$layout_table =<<<TTT
<div align="center" style="display:block;clear:both;margin:0;padding:0;">
   <table style="margin:0;table-layout:fixed;" width="100%" border="0" cellspacing="0" cellpadding="0">
     <tr valign="{$valign}">
       {$left_gap_html}
       {$table_columns}
       {$right_gap_html}
     </tr>
   </table>
</div>
TTT;

   return JAY__do_shortcode ($layout_table);
}
//===========================================================================

//===========================================================================
//
// Support for MemberWing-X membership plugin, http://www.memberwing.com
/*

[j-memberwing  conditions="*?"]
   .... only members who matches the 'condition' will see this
   [j-else/]
   .... only people who does not match the condition will see this
[/j-memberwing]

*/

function JAY__shortcode__jmemberwing ($atts, $content="")
{
   extract (shortcode_atts (
      array(
         'conditions'         => '?',         // Stuff in between {{{...}}} brackets. Ex: "gold" or "gold|silver"
         ),
         $atts));


   $condition_is_true   = FALSE;
   $mwx_installed       = TRUE;

   if (function_exists ('MWX__UserCanAccessArticle'))
      {
       // first parameter: article/page ID. -1 => current article, second  parameter: user_id. -1 => currently logged on user. Third parameter:  premium marker string (stuff inside {{{...}}} brackets)
      $access_info = MWX__UserCanAccessArticle (-1, -1, "gold:5d|platinum", FALSE);
      if ($access_info)
         {
         if ($access_info['immediate_access'])
            {
            // current visitor can access article protected with {{{gold:5d|platinum}}} premium marker immediately
            $condition_is_true = TRUE;
            }
         else
            {
            // Note: this will only work for MemberWing-X TSI Edition. Other editions will always return '0'.
            // current visitor can access article protected with  {{{gold:5d|platinum}}} premium marker in ' . $access_info['in_seconds'] .  ' seconds'
            $condition_is_true = FALSE;
            }
         }
      else
         {
         // current visitor does not have access to article protected with {{{gold:5d|platinum}}} premium marker
         $condition_is_true = FALSE;
         }
      }
   else
      {
      $condition_is_true = FALSE;
      $mwx_installed     = FALSE;
      }

   $content_arr = explode ('[j-else/]', $content);
   if (!isset($content_arr[1]))
      $content_arr[1] = '';

   $final_content = '';

   // action = "allow"
   if ($condition_is_true)
      {
      $final_content = $content_arr[0];
      }
   else
      {
      $final_content = $content_arr[1];
      if (!$mwx_installed)
         {
         $final_content .= '<h3 align="center"><span style="color:red;">Warning:</span> <a href="http://www.memberwing.com/">MemberWing-X</a> is not installed</h3>';
         }
      }

   return JAY__do_shortcode ($final_content);
}
//===========================================================================

//===========================================================================
//
// Allows embedding pages/posts inside of other pages or posts
/*

[jpage id="123"]

*/

function JAY__shortcode__jpage ($atts, $content="")
{
   extract (shortcode_atts (
      array(
         'id' => '',
         ),
         $atts));



   $page_id = $id;

   if (!$page_id)
      return "";

   // You must pass in a variable to the get_page function. If you pass in a value (e.g. get_page ( 123 ); ), Wordpress will generate an error.
   /*
   Object's members:
   [ID]                    => (integer)
   [post_author]           => (integer)
   [post_date]             => (YYYY-MM-DD HH:MM:SS)
   [post_date_gmt]         => (YYYY-MM-DD HH:MM:SS)
   [post_content]          => (all post content is in here)
   [post_title]            => (Post Title Here)
   [post_excerpt]          => (Post Excerpt)
   [post_status]           => (? | publish)
   [comment_status]        => (? | closed)
   [ping_status]           => (? | closed)
   [post_password]         => (blank if not specified)
   [post_name]             => (slug-is-here)
   [to_ping]               => (?)
   [pinged]                => (?)
   [post_modified]         => (YYYY-MM-DD HH:MM:SS)
   [post_modified_gmt]     => (YYYY-MM-DD HH:MM:SS)
   [post_content_filtered] => (?)
   [post_parent]           => (integer)
   [guid]                  => (a unique identifier that is not necessarily the URL to the Page)
   [menu_order]            => (integer)
   [post_type]             => (? | page)
   [post_mime_type]        => ()?)
   [comment_count]         => (integer)
   [ancestors]             => (object|array)
   [filter]                => (? | raw)
   */
   $page_data = get_page ($page_id);

   // Get Content and do all Wordpress filters including shortcodes.
   $content = apply_filters ('the_content', $page_data->post_content);

   return $content;
}
//===========================================================================

//===========================================================================
//
// BETA! subject to change
// Allows embed RSS feeds inside of any post, page or sidebar widget.
/*

[jfeed url="" items="*10"]

*/

function JAY__shortcode__jfeed ($atts, $content="")
{
   extract (shortcode_atts (
      array(
         'url'          => 'http://www.jshortcodes.com/feed/',
         'items'        => '10',

         'warnings'     => '0',                                      // Show feed warnings/error messages
         'msgerror'     => '<p>Fetch feed error. Bad feed URL?</p>', // Will be shown only if "warnings" is set to "1"
         'msgempty'     => '<p>No feed items found</p>',             // Will be shown only if "warnings" is set to "1"

         'templatefeed' => '<ul class="jfeed">{FEED_ITEMS}</ul>',
         'templateitem' => '<li class="jfeed_li"><img src="{ITEM_IMAGE}" style="float:left;margin-right:6px;height:50px;border:1px solid gray;{SHOW_IMAGE}" /><a target="_blank" href="{ITEM_PERMALINK}">{ITEM_TITLE}</a><div>{ITEM_CONTENT}</div></li>',

         'maxchars'     => "200",                                    // Maximum number of characters to show for each feed item. -1 = show full content, 0 = only title will be shown.
         ),
         $atts));

   if (!$url)
      return $warnings?$msgerror:"";

   // Get a SimplePie feed object from the specified feed source.
   $rss = fetch_feed ($url);

   if (!is_wp_error ($rss))
      {
      // Figure out how many total items there are, but with the upper limit
      $maxitems = $rss->get_item_quantity ($items);

      if (!$maxitems)
         return $warnings?$msgempty:"";

      // Build an array of all the items, starting with element 0 (first element).
      $rss_items_arr = $rss->get_items(0, $maxitems);

      $feed_items_html = "";

      foreach ($rss_items_arr as $item)
         {
         $item_permalink   = $item->get_permalink();
         $item_date        = $item->get_date  ('j F Y | g:i a');
         $item_title       = $item->get_title ();
         $item_content     = $item->get_content ();

         // Detect presence of image inside of feed content. If image is not present - suppress it via 'display:none;' CSS tag.
         $item_image = FALSE;
         if (preg_match_all ('@\<img[^\>]+src=[\'\"]([^\'\"]+)[\'\"]@i', $item_content, $matches, PREG_SET_ORDER))
            {
            foreach ($matches as $match)
            if (strpos (@$match[1], 'http://feeds.feedburner.com') === FALSE)
               {
               $item_image = $match[1];
               break;
               }
            }
         if (!$item_image)
            $show_image = 'display:none;';
         else
            $show_image = '';


         $item_content     = strip_tags ($item_content);
         $item_content     = substr ($item_content, 0, $maxchars);
         $item_content     = preg_replace ('@[^a-zA-Z0-9]+[a-zA-Z0-9]*$@', " ...", $item_content);

         $temp_output      = $templateitem;
         $temp_output      = str_replace ('{ITEM_IMAGE}',      $item_image,      $temp_output);
         $temp_output      = str_replace ('{SHOW_IMAGE}',      $show_image,      $temp_output);
         $temp_output      = str_replace ('{ITEM_PERMALINK}',  $item_permalink,  $temp_output);
         $temp_output      = str_replace ('{ITEM_TITLE}',      $item_title,      $temp_output);
         $temp_output      = str_replace ('{ITEM_DATE}',       $item_date,       $temp_output);
         $temp_output      = str_replace ('{ITEM_CONTENT}',    $item_content,    $temp_output);

         $feed_items_html          .= $temp_output;
         }

      $output = str_replace ('{FEED_ITEMS}', $feed_items_html, $templatefeed);
      }
   else
      return $warnings?$msgerror:"";

   return $output;
}
//===========================================================================

//===========================================================================
//
// Allows embed RSS feeds inside of any post, page or sidebar widget.
/*

[jtabs size="xxxsmall|xxsmall|xsmall|small|*normal" theme="blitzer|cupertino|overcast|*smoothness|vader"]
      Hello World::
      This is hello world. This
      is wonderful article
   [jtab/]
      Yes!::
      This is second tab
[/jtabs]

*/

function JAY__shortcode__jtabs ($atts, $content="")
{
   global  $g_theme_unavail_message;

   extract (shortcode_atts (
      array(
         'theme'        => 'smoothness',
         'size'         => 'normal',
         ),
         $atts));

   $jay_settings = JAY__get_settings();

   if (!$jay_settings['jquery_themes'][$theme])
      {
      $unavail_msg = str_replace ('{THEME}', $theme, $g_theme_unavail_message);
      $theme = 'smoothness';
      }
   else
      {
      $unavail_msg = "";
      }

$jtabs_template=<<<TTT
<div class="{$size} jayq-all jayq-{$theme}">
   <div class="jtabs">
      <ul>
         {{{LI_ELEMENTS}}}
      </ul>
         {{{DIV_ELEMENTS}}}
   </div>
</div>
TTT;

   $content_arr = explode ('[jtab/]', $content);
   $li_elements = "";
   $div_elements = "";
   foreach ($content_arr as $idx=>$content_el)
      {
      $tab_data = explode ('::', $content_el, 2);
      if (count($tab_data) != 2)
         {
         $tab_data = explode (' ', $content_el, 2);
         }

      $li_elements  .= ('<li><a href="#jtabs-' . strval($idx+1) . '">' . JAY__trim_br($tab_data[0]) . '</a></li>');
      $div_elements .= ('<div id="jtabs-' . strval($idx+1) . '">' . $unavail_msg . JAY__trim_br($tab_data[1]) . '</div>');
      }

   $output = $jtabs_template;
   $output = str_replace ('{{{LI_ELEMENTS}}}',  $li_elements,  $output);
   $output = str_replace ('{{{DIV_ELEMENTS}}}', $div_elements, $output);

   return JAY__do_shortcode ($output);
}
//===========================================================================

//===========================================================================
//
// Allows embed RSS feeds inside of any post, page or sidebar widget.
/*

[jaccordion size="xxxsmall|xxsmall|xsmall|small|*normal" theme="blitzer|cupertino|overcast|*smoothness|vader" active="*"]
      Hello World::
      This is hello world. This
      is wonderful article
   [jacc/]
      Yes!::
      This is second tab
[/jaccordion]

*/

function JAY__shortcode__jaccordion ($atts, $content="")
{
   global  $g_theme_unavail_message;

   extract (shortcode_atts (
      array(
         'theme'        => 'smoothness',
         'size'         => 'normal',
         'active'       => FALSE,            // 1-based active panel
         ),
         $atts));

   $jay_settings = JAY__get_settings();

   // Note: admin pages preloads all themes
   if (!$jay_settings['jquery_themes'][$theme] && !is_admin())
      {
      $unavail_msg = str_replace ('{THEME}', $theme, $g_theme_unavail_message);
      $theme = 'smoothness';
      }
   else
      {
      $unavail_msg = "";
      }

   if ($active > 0)
      {
      $active--;
      $active_pane = "active_pane=\"{$active}\"";
      }
   else
      $active_pane = "";

$jtabs_template=<<<TTT
<div class="jayq-all {$size} jayq-{$theme}">
   <div {$active_pane} class="jaccordion">
       {{{DIV_ELEMENTS}}}
   </div>
</div>
TTT;

   $content_arr = explode ('[jacc/]', $content);
   $div_elements = "";
   foreach ($content_arr as $idx=>$content_el)
      {
      $tab_data = explode ('::', $content_el, 2);
      if (count($tab_data) != 2)
         {
         $tab_data = explode (' ', $content_el, 2);
         }

      $div_elements .= '<div><a href="#">' . JAY__trim_br($tab_data[0]) . '</a></div><div>' . $unavail_msg . JAY__trim_br($tab_data[1]) . '</div>';
      }

   $output = $jtabs_template;
   $output = str_replace ('{{{DIV_ELEMENTS}}}', $div_elements, $output);

   return JAY__do_shortcode ($output);
}
//===========================================================================


?>