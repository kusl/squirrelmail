<?php

/**
 * view_text.php -- Displays the main frameset
 *
 * Copyright (c) 1999-2006 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Who knows what this file does. However PUT IT HERE DID NOT PUT
 * A SINGLE FREAKING COMMENT IN! Whoever is responsible for this,
 * be very ashamed.
 *
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
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/mime.php');
require_once(SM_PATH . 'functions/html.php');

sqgetGlobalVar('key',        $key,          SQ_COOKIE);
sqgetGlobalVar('username',   $username,     SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad,   SQ_SESSION);
sqgetGlobalVar('delimiter',  $delimiter,    SQ_SESSION);
sqgetGlobalVar('QUERY_STRING', $QUERY_STRING, SQ_SERVER);
sqgetGlobalVar('messages', $messages);
sqgetGlobalVar('passed_id', $passed_id, SQ_GET);

if ( sqgetGlobalVar('mailbox', $temp, SQ_GET) ) {
  $mailbox = $temp;
}
if ( !sqgetGlobalVar('ent_id', $ent_id, SQ_GET) ) {
  $ent_id = '';
}
if ( !sqgetGlobalVar('passed_ent_id', $passed_ent_id, SQ_GET) ) {
  $passed_ent_id = '';
} 



$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 0);
$mbx_response = sqimap_mailbox_select($imapConnection, $mailbox);

$message = &$messages[$mbx_response['UIDVALIDITY']][$passed_id];
$message_ent = $message->getEntity($ent_id);
if ($passed_ent_id) {
    $message = &$message->getEntity($passed_ent_id);
}
$header   = $message_ent->header;
$type0    = $header->type0;
$type1    = $header->type1;
$charset  = $header->getParameter('charset');
$encoding = strtolower($header->encoding);

$msg_url   = 'read_body.php?' . $QUERY_STRING;
$msg_url   = set_url_var($msg_url, 'ent_id', 0);
$dwnld_url = '../src/download.php?' . $QUERY_STRING . '&amp;absolute_dl=true';

$body = mime_fetch_body($imapConnection, $passed_id, $ent_id);
$body = decodeBody($body, $encoding);

if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
    function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
    if (mb_detect_encoding($body) != 'ASCII') {
        $body = $languages[$squirrelmail_language]['XTRA_CODE']('decode', $body);
    }
}

if ($type1 == 'html' || (isset($override_type1) &&  $override_type1 == 'html')) {
    $body = MagicHTML( $body, $passed_id, $message, $mailbox);
    // html attachment with character set information
    if (! empty($charset))
        $body = charset_decode($charset,$body,false,true);
} else {
    translateText($body, $wrap_at, $charset);
}

displayPageHeader($color, 'None');
?>
<br /><table width="100%" border="0" cellspacing="0" cellpadding="2" align="center"><tr><td bgcolor="<?php echo $color[0]; ?>">
<b><center>
<?php
echo _("Viewing a text attachment") . ' - ' .
    '<a href="'.$msg_url.'">' . _("View message") . '</a>';
?>
</b></td><tr><tr><td><center>
<?php
echo '<a href="' . $dwnld_url . '">' . _("Download this as a file") . '</a>';
?>
</center><br />
</center></b>
</td></tr></table>
<table width="98%" border="0" cellspacing="0" cellpadding="2" align="center"><tr><td bgcolor="<?php echo $color[0]; ?>">
<tr><td bgcolor="<?php echo $color[4]; ?>"><tt>
<?php echo $body; ?>
</tt></td></tr></table>
</body></html>