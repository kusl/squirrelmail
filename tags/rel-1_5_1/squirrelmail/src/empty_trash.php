<?php

/**
 * empty_trash.php
 *
 * Handles deleting messages from the trash folder without
 * deleting subfolders.
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/display_messages.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/tree.php');

/* get those globals */

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

/* finished globals */

$imap_stream = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);

$mailbox = $trash_folder;
$boxes = sqimap_mailbox_list($imap_stream);

/*
 * According to RFC2060, a DELETE command should NOT remove inferiors (sub folders)
 *    so lets go through the list of subfolders and remove them before removing the
 *    parent.
 */

/** First create the top node in the tree **/
$numboxes = count($boxes);
for ($i = 0; $i < $numboxes; $i++) {
    if (($boxes[$i]['unformatted'] == $mailbox) && (strlen($boxes[$i]['unformatted']) == strlen($mailbox))) {
        $foldersTree[0]['value'] = $mailbox;
        $foldersTree[0]['doIHaveChildren'] = false;
        continue;
    }
}
/*
 * Now create the nodes for subfolders of the parent folder
 * You can tell that it is a subfolder by tacking the mailbox delimiter
 *    on the end of the $mailbox string, and compare to that.
 */
$j = 0;
for ($i = 0; $i < $numboxes; $i++) {
    if (substr($boxes[$i]['unformatted'], 0, strlen($mailbox . $delimiter)) == ($mailbox . $delimiter)) {
        addChildNodeToTree($boxes[$i]['unformatted'], $boxes[$i]['unformatted-dm'], $foldersTree);
    }
}

// now lets go through the tree and delete the folders
walkTreeInPreOrderEmptyTrash(0, $imap_stream, $foldersTree);
// update mailbox cache
$mailboxes=sqimap_get_mailboxes($imap_stream,true,$show_only_subscribed_folders);
sqimap_logout($imap_stream);

// close session properly before redirecting
session_write_close();

$location = get_location();
header ("Location: $location/left_main.php");

?>