<?php
   /**
    **  newmails_opt.php
    **
    **  Displays all options relating to new mail sounds
    **
    **/

   chdir("..");

   session_start();

   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($display_messages_php))
      include("../functions/display_messages.php");
   if (!isset($imap_php))
      include("../functions/imap.php");
   if (!isset($array_php))
      include("../functions/array.php");
   if (!isset($i18n_php))
      include("../functions/i18n.php");

   include("../src/load_prefs.php");
   displayPageHeader($color, "None");

   $media_enable = getPref($data_dir,$username,"newmail_enable");
   if($media_enable == "") {
     $media_enable = "FALSE";
   }

  $media_popup = getPref($data_dir, $username,"newmail_popup");
  $media_allbox = getPref($data_dir,$username,"newmail_allbox");
  $media_recent = getPref($data_dir,$username,"newmail_recent");

   $media = getPref($data_dir,$username,"newmail_media");
   if ($media == "") {
     $media = "../plugins/newmail/sounds/Notify.wav";
   }

?>
   <br>
   <table width=95% align=center border=0 cellpadding=2 cellspacing=0><tr><td bgcolor="<?php echo $color[0] ?>">
      <center><b><?php echo _("Options") ?> - New Mail Notification v1.2</b></center>
   </td></tr></table>
<p>
Select <b>Enable Media Playing</b> to turn on playing a media file when
unseen mail is in your folders. When enabled, you can specify the media file to play in the provided
file box.<p>

The <b>Check all boxes, not just INBOX</b> option will check ALL of your
folders for unseen mail, not just the inbox for notification.<p>

Selecting the <b>Show popup</b> option will enable the showing of a popup
window when unseen mail is in your folders (requires JavaScript).<p>

Use the <b>Check RECENT</b> to only check for messages that are recent.  
Recent messages are those that have just recently showed up and have not
been "viewed" or checked yet.  This can prevent being continuously annoyed
by sounds or popups for unseen mail.<p>

Select from the list of <b>server files</b> the media file to play when
new mail arrives.  Selecting <b>local media</b> will play the file
specified in the <b>local media file</b> box to play from the local
computer.  If no file is specified, the system will use a default from the
server.

   <form action="../../src/options.php" method=post>
      <table width=100% cellpadding=0 cellspacing=2 border=0>
        <tr>
            <td align=right nowrap>&nbsp
            </td><td>
               <input type=checkbox <?php
		  	if($media_enable == "on") {
			  echo "checked";
			}?>
		name=media_enable><b> Enable Media Playing</b> 
            </td>
         </tr>
        <tr>
            <td align=right nowrap>&nbsp
            </td><td>
               <input type=checkbox <?php
		  	if($media_allbox == "on") {
			  echo "checked";
			}?>
		name=media_allbox><b> Check all boxes, not just INBOX</b>
            </td>
         </tr>
        <tr>
            <td align=right nowrap>&nbsp
            </td><td>
               <input type=checkbox <?php
		  	if($media_recent == "on") {
			  echo "checked";
			}?>
		name=media_recent><b> Count only messages that are RECENT</b>
            </td>
         </tr>
        <tr>
            <td align=right nowrap>&nbsp
            </td><td>
               <input type=checkbox <?php
		  	if($media_popup == "on") {
			  echo "checked";
			}?>
		name=media_popup><b> Show popup window on new mail</b>
		&nbsp;(requires JavaScript to work) 
            </td>
         </tr>
        <tr>
            <td align=right nowrap>Select server file:
            </td><td>
               <SELECT NAME=media_sel><?php

		echo "<OPTION VALUE=\"(local media)\">(local media)";

		// Iterate sound files for options

		$d = dir("../plugins/newmail/sounds");
		while($entry=$d->read()) {
		   $fname = $d->path . "/" . $entry;
		   if ($entry != ".." && $entry != ".") { 
		   	echo "<OPTION ";
			if ($fname == $media) {
			    echo "SELECTED ";
			}			
			echo "VALUE=\"" . $fname . "\">" . $entry . "\n";
		   }
		}
		$d->close();

		?>
	       </SELECT> 
            </td>
         </tr>
         <tr>
            <td align=right nowrap>Local Media File :
            </td><td>
               <input type=file size=40 name=media_file>
            </td>
         </tr>
         <tr>
            <td align=right nowrap>Current File:
            </td><td>
	       <input type=hidden value=\"" <?php echo $media; ?>
			name=media_default>
	       <?php echo $media; ?> 
            </td>
         </tr>
         <tr>
            <td>&nbsp;
            </td><td>
               <input type="submit" value="Submit" name="submit_newmail">
            </td>
         </tr>
      </table>   
   </form>
</body></html>
