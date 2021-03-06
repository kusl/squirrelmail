<?php

/* This button fills out a form with your setup information already
   gathered -- all you have to do is type. */


/* Initialize the bug report plugin */
function squirrelmail_plugin_init_bug_report()
{
  global $squirrelmail_plugin_hooks;

  $squirrelmail_plugin_hooks['menuline']['bug_report'] = 'bug_report_button';
  $squirrelmail_plugin_hooks['options_display_inside']['bug_report'] = 'bug_report_options';
  $squirrelmail_plugin_hooks['options_display_save']['bug_report'] = 'bug_report_save';
  $squirrelmail_plugin_hooks['loading_prefs']['bug_report'] = 'bug_report_load';
}


/* Show the button in the main bar */
function bug_report_button() 
{
  global $color, $bug_report_visible;

  if (! $bug_report_visible) 
    return;
    
  displayInternalLink('plugins/bug_report/bug_report.php', 'Bug', '');
  echo "&nbsp;&nbsp;\n";
}


function bug_report_save() 
{
  global $username,$data_dir;
  global $bug_report_bug_report_visible;

  if (isset($bug_report_bug_report_visible)) 
  {
    setPref($data_dir, $username, 'bug_report_visible', '1');
  } 
  else 
  {
    setPref($data_dir, $username, 'bug_report_visible', '');
  }
}


function bug_report_load()
{ 
  global $username, $data_dir;
  global $bug_report_visible;

  $bug_report_visible = getPref($data_dir, $username, 'bug_report_visible');
}


function bug_report_options()
{
  global $bug_report_visible;
  
  echo "<tr><td align=right nowrap>Bug Reports:</td>\n";
  echo "<td><input name=\"bug_report_bug_report_visible\" type=CHECKBOX";
  if ($bug_report_visible)
    echo " CHECKED";
  echo "> Show button in toolbar</td></tr>\n";
}

?>