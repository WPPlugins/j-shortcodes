<?php
/*
Plugin Name: J Shortcodes
Plugin URI: http://www.jshortcodes.com/
Version: 1.216
Author: Gleb Esman, http://www.jshortcodes.com/
Author URI: http://www.jshortcodes.com/
Description: Collection of useful shortcodes to create custom column layouts, add custom buttons, content boxes, feature and call to action boxes. Pick any color or size for any element. Create sophisticated column layouts directly within any page, post or even sidebar widget. Check out <a href="http://www.jshortcodes.com/shortcodes/">J Shortcodes samples, demos and tutorials</a>.
*/

define('J_SHORTCODES_VERSION',  '1.216');

include (dirname(__FILE__) . '/j-include-all.php');

//---------------------------------------------------------------------------
// Plugins actions, hooks and filters
add_action                 ('init',                      'JAY__init',                  10);
add_action                 ('wp_head',                   'JAY__wp_head',               10);
add_action                 ('admin_menu',                'JAY__admin_menu'               );
//---------------------------------------------------------------------------


//---------------------------------------------------------------------------
// Shortcodes
// [jbutton]
add_shortcode              ('jbox',                      'JAY__shortcode__jbox');
add_shortcode              ('jbutton',                   'JAY__shortcode__jbutton');
add_shortcode              ('jcolumns',                  'JAY__shortcode__jcolumns');
add_shortcode              ('jbuttonify',                'JAY__shortcode__jbuttonify');
//---------------------------------------------------------------------------


//===========================================================================
function JAY__init ()
{
   // Make sure jQuery is loaded.

   wp_enqueue_script ('jquery');

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
   global $g_JAY__plugin_directory_url;

   // Force load stylesheet and .js for 't1' default template.
?>
<link rel="stylesheet" type="text/css" href="<?php echo $g_JAY__plugin_directory_url . '/css/jay.css'; ?>" />
<script type="text/javascript" src="<?php echo $g_JAY__plugin_directory_url . '/js/jay.js'; ?>"></script>

<?php

}
//===========================================================================

//===========================================================================
function JAY__admin_menu ()
{
   global $g_JAY__plugin_directory_url;

   add_menu_page    (
      'J-Shortcodes General Settings',           // Page Title
      '<div align="center" style="font-size:90%;"><b>J</b>-Shortcodes</div>',              // Menu Title - lower corner of admin menu
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
<div align="center" style="background-color:#FFA;padding:5px;font-size:120%;border: 1px solid gray;margin:5px;">
Settings updated!
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_jay_settings']))
      {
      JAY__reset_all_settings ();
echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:120%;border: 1px solid gray;margin:5px;">
All settings reverted to all defaults
</div>
HHHH;
      }
   else if (isset($_POST['button_reset_partial_jay_settings']))
      {
      JAY__reset_partial_settings ();
echo <<<HHHH
<div align="center" style="background-color:#FFA;padding:5px;font-size:120%;border: 1px solid gray;margin:5px;">
Settings on this page reverted to defaults
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

   return do_shortcode ($output_html);
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

   return do_shortcode ($final_html);
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

   return do_shortcode ($box_html);
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

   return do_shortcode ($layout_table);
}
//===========================================================================

//===========================================================================
// Trim <br />, <br/> and <br> from edges of content
function JAY__trim_br ($content)
{
   // Strip one <br /> from the edges of content. They are force added by Wordpress. Not what user intended in this case.
   $content = trim ($content);
   $h = substr ($content, 0, 6);
   if ($h == '<br />')
      $content = substr ($content, 6);        // strip heading '<br />'
   else if (strpos ($h, "<br/>") === 0)
      $content = substr ($content, 5);        // strip heading '<br/>'
   else if (strpos ($h, "<br>") === 0)
      $content = substr ($content, 4);        // strip heading '<br>'

   $t = substr ($content, -6, 6);
   if ($t == '<br />')
      $content = substr ($content, 0, -6);    // strip trailing '<br />'
   else if (@strpos ($t, "<br/>", 1) === 1)
      $content = substr ($content, 0, -5);    // strip heading '<br/>'
   else if (@strpos ($t, "<br>",  2) === 2)
      $content = substr ($content, 0, -4);    // strip heading '<br>'

   return $content;
}
//===========================================================================

?>