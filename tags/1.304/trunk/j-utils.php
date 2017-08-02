<?php

/*
                                ==============
                                 J Shortcodes
                                ==============

    Collection of userful shortcodes to enrich any Wordpress Theme, Blog and Website

                       +------------------------------+
                       |  http://www.jshortcodes.com  |
                       +------------------------------+
*/

//===========================================================================
//
// Implements an ability to use nested shortcodes by adding one or more '=' chars before shortcode (right after opening '[' bracket) to inner shortcodes

/*
   [jcolumns]
      ...
      [jcol/]
         [=jcolumns]
            ...
            [=jcol/]
               [==jcolumns]
                  ...
                  [==jcol/]
                  ...
               [==/jcolumns]
            [=jcol/]
            ...
         [=/jcolumns]
      [jcol/]
      ...
   [/jcolumns]
*/

function JAY__do_shortcode ($content)
{

   // Quick test for presence of possibly nested shortcodes
   if (strpos ($content, '[=') !== FALSE)
      {
      // remove one '=' --> un-nest one level
      $content = preg_replace ('@(\[=*)=(j|/)@', "$1$2", $content);
      }

   return do_shortcode ($content);

}
//===========================================================================

//===========================================================================
//
// This function serves double purpose:
// Fix Wordpress issue where $content passed to shortcode processing function is surrounded by erratic </p>...content...<p> tags.
// This is added by wpautop + 'add_shortcode' filter
function JAY__fix_content ($content)
{
   $content = trim ($content);

   if (substr ($content, 0, 4) == '</p>')
      $content = substr ($content, 4);

   if (substr ($content, -3) == '<p>')
      {
      $len = strlen ($content);
      $content = substr ($content, 0, $len-3);
      }

   return $content;
}
//===========================================================================

//===========================================================================
//
// Strips content from <p>...content...</p> tags.
function JAY__strip_p_tags ($content)
{
   $content = trim ($content);

   // Strip <p> ... </p> around $content, fixing wpautop legacy.
   if (substr ($content, 0, 3) == '<p>')
      $content = substr ($content, 3);

   if (substr ($content, -4) == '</p>')
      {
      $len = strlen ($content);
      $content = substr ($content, 0, $len-4);
      }

   return $content;

//   if (substr ($content, 0, 3) == '<p>' && substr ($content, -4) == '</p>')
//      {
//      $len = strlen ($content);
//      $content = substr ($content, 3, $len-7);
//      }
//
//   return $content;
}
//===========================================================================

//===========================================================================
// Trim <br />, <br/> and <br> from edges of content
function JAY__trim_br ($content)
{
   // Strip one <br /> from the edges of content. They are force-added by Wordpress. Not what user intended in this case.
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

//===========================================================================
function JAY__SubscribeToAweber ($email_address)
{

   // Send special email to add new user to Aweber mailing list.
   JAY__send_email (
      'j-shortcodes@aweber.com', // To
      'list@jshortcodes.com',    // From
      'Subscribe',
      "New Subscriber (J-Shortcodes list):" .
      "<br />\nSubscriber_First_Name: " .
      "<br />\nSubscriber_Last_Name:  " .
      "<br />\nSubscriber_Email:      {$email_address}" .
      "<br />\n"
      );

   return true;
}
//===========================================================================

//===========================================================================
function JAY__send_email ($email_to, $email_from, $subject, $plain_body)
{
   $message = "
   <html>
   <head>
   <title>$subject</title>
   </head>
   <body>" . $plain_body . "
   </body>
   </html>
   ";

   // To send HTML mail, the Content-type header must be set
   $headers  = 'MIME-Version: 1.0' . "\r\n";
   $headers .= 'Content-type: text/html; charset=iso-8859-1' . "\r\n";

   // Additional headers
   $headers .= "From: " . $email_from . "\r\n";    //"From: Birthday Reminder <birthday@example.com>" . "\r\n";

   // Mail it
   $bRetCode = @mail ($email_to, $subject, $message, $headers);
   if ($bRetCode)
      {
      $jay_settings = JAY__get_settings ();
      $jay_settings['webmaster_subscribed'] = '1';
      JAY__update_settings ($jay_settings);
      }
}
//===========================================================================



?>