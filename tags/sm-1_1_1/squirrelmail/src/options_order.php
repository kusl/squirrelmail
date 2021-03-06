<?php
   /**
    **  options_highlight.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Displays message highlighting options
    **
    **  $Id$
    **/

   include('../src/validate.php');
   include('../functions/strings.php');
   include('../config/config.php');
   include('../functions/page_header.php');
   include('../functions/display_messages.php');
   include('../functions/imap.php');
   include('../functions/array.php');
   include('../functions/i18n.php');
   include('../functions/plugin.php');


   if (! isset($action)) { $action = ''; }
   if ($action == 'delete' && isset($theid)) {
      removePref($data_dir, $username, "highlight$theid");
   } else if ($action == 'save') {
   } 
   include('../src/load_prefs.php');
   displayPageHeader($color, 'None');
?>
   <br>
   <table width=95% align=center border=0 cellpadding=2 cellspacing=0><tr><td bgcolor="<?php echo $color[0] ?>">
      <center><b><?php echo _("Options") . " - " . _("Index Order"); ?></b></center>
   </td></tr></table>

   <table width=95% align=center border=0><tr><td>
<?php

   $available[1] = _("Checkbox");
   $available[2] = _("From");
   $available[3] = _("Date");
   $available[4] = _("Subject");
   $available[5] = _("Flags");
   $available[6] = _("Size");
   
   if (! isset($method)) { $method = ''; }

   if ($method == 'up' && $num > 1) {
      $prev = $num-1;
      $tmp = $index_order[$prev];
      $index_order[$prev] = $index_order[$num];
      $index_order[$num] = $tmp;
   } else if ($method == 'down' && $num < count($index_order)) {
      $next = $num++;
      $tmp = $index_order[$next];
      $index_order[$next] = $index_order[$num];
      $index_order[$num] = $tmp;
   } else if ($method == 'remove' && $num) {
      for ($i=1; $i < 8; $i++) {
         removePref($data_dir, $username, "order$i"); 
      }
      for ($j=1,$i=1; $i <= count($index_order); $i++) {
         if ($i != $num) {
            $new_ary[$j] = $index_order[$i];
            $j++;
         }
      }
      $index_order = array();
      $index_order = $new_ary;
      if (count($index_order) < 1) {
         include ('../src/load_prefs.php');
      }
   } else if ($method == 'add' && $add) {
      // User should not be able to insert PHP-code here
      $add = str_replace ('<?', '..', $add);
      $add = ereg_replace ('<.*script.*language.*php.*>', '..', $add);
      $add = str_replace ('<%', '..', $add);
      $index_order[count($index_order)+1] = $add;
   }

   if ($method) {
      for ($i=1; $i <= count($index_order); $i++) {
         setPref($data_dir, $username, "order$i", $index_order[$i]);
      }
   }
   echo '<center>';
   echo '<table cellspacing="0" cellpadding="0" border="0" width="65%"><tr><td>' . "\n";
   echo _("The index order is the order that the columns are arranged in the message index.  You can add, remove, and move columns around to customize them to fit your needs.");
   echo '</td></tr></table></center><br>';

   if (count($index_order))
   {
      echo '<center>';
      echo '<table cellspacing="0" cellpadding="0" border="0">' . "\n";
      for ($i=1; $i <= count($index_order); $i++) {
         $tmp = $index_order[$i];
         echo '<tr>';
         echo "<td><small><a href=\"options_order.php?method=up&num=$i\">". _("up") ."</a></small></td>\n";
         echo '<td><small>&nbsp;|&nbsp;</small></td>' . "\n";
         echo "<td><small><a href=\"options_order.php?method=down&num=$i\">". _("down") . "</a></small></td>\n";
         echo '<td><small>&nbsp;|&nbsp;</small></td>' . "\n";
         echo '<td>';
         // Always show the subject
         if ($tmp != 4)
            echo "<small><a href=\"options_order.php?method=remove&num=$i\">" . _("remove") . '</a></small>';
         echo "</td>\n";
         echo '<td><small>&nbsp;-&nbsp;</small></td>' . "\n";
         echo '<td>' . $available[$tmp] . "</td>\n";
         echo "</tr>\n";
      }
      echo "</table>\n";
      echo '</center>';
   }
   
   if (count($index_order) != count($available)) {
   echo '<center><form name="f" method="post" action="options_order.php">';
   echo '<select name="add">';
   for ($i=1; $i <= count($available); $i++) {
      $found = false;
      for ($j=1; $j <= count($index_order); $j++) {
         if ($index_order[$j] == $i) {
            $found = true;
         }
      }
      if (!$found) {
         echo "<option value=\"$i\">$available[$i]</option>";
      }
   }
   echo '</select>';
   echo '<input type="hidden" value="add" name="method">';
   echo '<input type="submit" value="'._("Add").'" name="submit">';
   echo '</form></center>';
   }

   echo '<br><center><a href="../src/options.php">' . _("Return to options page") . '</a></center>';

?>
   </td></tr></table>
</body></html>
