<?php
   /**
    **  delete_message.php
    **
    **  Copyright (c) 1999-2000 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **  Deletes a meesage from the IMAP server 
    **
    **  $Id$
    **/

   session_start();

   if (!isset($strings_php))
      include("../functions/strings.php");
   if (!isset($config_php))
      include("../config/config.php");
   if (!isset($page_header_php))
      include("../functions/page_header.php");
   if (!isset($display_message_php))
      include("../functions/display_messages.php");
   if (!isset($imap_php))
      include("../functions/imap.php");

   include("../src/load_prefs.php");

   $imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
   sqimap_mailbox_select($imapConnection, $mailbox);

   sqimap_messages_delete($imapConnection, $message, $message, $mailbox);
   if ($auto_expunge)
      sqimap_mailbox_expunge($imapConnection, $mailbox, true);

   $location = get_location();
   if (isset($where) && isset($what))
      header ("Location: $location/search.php?where=".urlencode($where)."&what=".urlencode($what)."&mailbox=".urlencode($mailbox));
   else   
      header ("Location: $location/right_main.php?sort=$sort&startMessage=$startMessage&mailbox=".urlencode($mailbox));

   sqimap_logout($imapConnection);
?>
