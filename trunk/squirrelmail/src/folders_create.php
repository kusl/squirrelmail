<?
   include("../config/config.php");
   include("../functions/strings.php");
   include("../functions/page_header.php");
   include("../functions/imap.php");
   include("../functions/display_messages.php");

   include("../src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, 0);
   $dm = sqimap_get_delimiter($imapConnection);

   if (strpos($folder_name, "\"") || strpos($folder_name, ".") ||
       strpos($folder_name, "/") || strpos($folder_name, "\\") ||
       strpos($folder_name, "'") || strpos($folder_name, "$dm")) {
      plain_error_message(_("Illegal folder name.  Please select a different name.<BR><A HREF=\"../src/folders.php\">Click here to go back</A>."), $color);
      exit;
   }

   if ($contain_subs == true)
      $folder_name = "$folder_name$dm";

   if (trim($subfolder) == "[ None ]") {
      sqimap_mailbox_create ($imapConnection, $folder_name, "");
   } else {
      sqimap_mailbox_create ($imapConnection, $subfolder.$dm.$folder_name, "");
   }
   fputs($imapConnection, "1 logout\n");

//   if ($auto_forward == true) {
//      header ("Location: webmail.php?right_frame=folders.php");
//   } else {
      echo "<html><BODY TEXT=\"$color[8]\" BGCOLOR=\"$color[4]\" LINK=\"$color[7]\" VLINK=\"$color[7]\" ALINK=\"$color[7]\">\n";
      displayPageHeader($color, "None");
      echo "<FONT FACE=\"Arial,Helvetica\">";
      echo "<BR><BR><BR><CENTER><B>";
      echo _("Folder Created!");
      echo "</B><BR><BR>";
      echo _("The folder has been successfully created.");
      echo "<BR><A HREF=\"webmail.php?right_frame=folders.php\" TARGET=_top>";
      echo _("Click here");
      echo "</A> ";
      echo _("to continue.");
      echo "</CENTER></FONT>";
      echo "</BODY></HTML>";
//   }
?>

