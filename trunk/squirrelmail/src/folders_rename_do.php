<?
   session_start();

   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($imap_php))
      include("../functions/imap.php");

   include("../src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   $dm = sqimap_get_delimiter($imapConnection);

   if (strpos($orig, $dm))
      $old_dir = substr($orig, 0, strrpos($orig, $dm));
   else
      $old_dir = "";

   if ($old_dir != "")
      $newone = "$old_dir$dm$new_name";
   else
      $newone = "$new_name";

   $orig = stripslashes($orig);
   $newone = stripslashes($newone);

   fputs ($imapConnection, ". RENAME \"$orig\" \"$newone\"\n");
   $data = sqimap_read_data($imapConnection, ".", true, $a, $b);

   // Renaming a folder doesn't renames the folder but leaves you unsubscribed
   //    at least on Cyrus IMAP servers.
   if ($isfolder) {
      $newone = $newone.$dm;
      $orig = $orig.$dm;
   }   

   sqimap_unsubscribe($imapConnection, $orig);
   sqimap_subscribe($imapConnection, $newone);

   /** Log out this session **/
   sqimap_logout($imapConnection);

   echo "<HTML><BODY TEXT=\"$color[8]\" BGCOLOR=\"$color[4]\" LINK=\"$color[7]\" VLINK=\"$color[7]\" ALINK=\"$color[7]\">\n";
   displayPageHeader($color, "None");
   echo "<BR><BR><BR><CENTER><B>";
   echo _("Folder Renamed!");
   echo "</B><BR><BR>";
   echo _("The folder has been successfully renamed.");
   echo "<BR><A HREF=\"webmail.php?PHPSESSID=$PHPSESSID&right_frame=folders.php\" TARGET=_top>";
   echo _("Click here");
   echo "</A> ";
   echo _("to continue.");
   echo "</CENTER>";
   
   echo "</BODY></HTML>"; 
?>
