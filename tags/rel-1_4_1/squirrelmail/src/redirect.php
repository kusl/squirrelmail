<?php

/**
* redirect.php
* Derived from webmail.php by Ralf Kraudelt <kraude@wiwi.uni-rostock.de>
*
* Copyright (c) 1999-2003 The SquirrelMail Project Team
* Licensed under the GNU GPL. For full terms see the file COPYING.
*
* Prevents users from reposting their form data after a successful logout.
*
* $Id$
*/

/* Path for SquirrelMail required files. */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/i18n.php');
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'config/config.php');
require_once(SM_PATH . 'functions/prefs.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/constants.php');
require_once(SM_PATH . 'functions/page_header.php');

/* Before starting the session, the base URI must be known. Assuming */
/* that this file is in the src/ subdirectory (or something).        */
if (!function_exists('sqm_baseuri')){
    require_once(SM_PATH . 'functions/display_messages.php');
}
$base_uri = sqm_baseuri();

header('Pragma: no-cache');
$location = get_location();

session_set_cookie_params (0, $base_uri);
session_start();

sqsession_unregister ('user_is_logged_in');
sqsession_register ($base_uri, 'base_uri');

/* get globals we me need */
sqGetGlobalVar('login_username', $login_username);
sqGetGlobalVar('secretkey', $secretkey);
sqGetGlobalVar('js_autodetect_results', $js_autodetect_results);
if(!sqGetGlobalVar('squirrelmail_language', $squirrelmail_language) || $squirrelmail_language == '') {
	$squirrelmail_language = $squirrelmail_default_language;
}

/* end of get globals */

set_up_language($squirrelmail_language, true);
/* Refresh the language cookie. */
setcookie('squirrelmail_language', $squirrelmail_language, time()+2592000, 
          $base_uri);

if (!isset($login_username)) {
    include_once(SM_PATH .  'functions/display_messages.php' );
    logout_error( _("You must be logged in to access this page.") );    
    exit;
}

if (!sqsession_is_registered('user_is_logged_in')) {
    do_hook ('login_before');

    $onetimepad = OneTimePadCreate(strlen($secretkey));
    $key = OneTimePadEncrypt($secretkey, $onetimepad);
    sqsession_register($onetimepad, 'onetimepad');

    /* remove redundant spaces */
    $login_username = trim($login_username);

    /* Verify that username and password are correct. */
    if ($force_username_lowercase) {
        $login_username = strtolower($login_username);
    }

    $imapConnection = sqimap_login($login_username, $key, $imapServerAddress, $imapPort, 0);

    $sqimap_capabilities = sqimap_capability($imapConnection);
    sqsession_register($sqimap_capabilities, 'sqimap_capabilities');
    $delimiter = sqimap_get_delimiter ($imapConnection);

    sqimap_logout($imapConnection);
    sqsession_register($delimiter, 'delimiter');

    $username = $login_username;
    sqsession_register ($username, 'username');
    setcookie('key', $key, 0, $base_uri);
    do_hook ('login_verified');

}

/* Set the login variables. */
$user_is_logged_in = true;
$just_logged_in = true;

/* And register with them with the session. */
sqsession_register ($user_is_logged_in, 'user_is_logged_in');
sqsession_register ($just_logged_in, 'just_logged_in');

/* parse the accepted content-types of the client */
$attachment_common_types = array();
$attachment_common_types_parsed = array();
sqsession_register($attachment_common_types, 'attachment_common_types');
sqsession_register($attachment_common_types_parsed, 'attachment_common_types_parsed');

$debug = false;

if ( sqgetGlobalVar('HTTP_ACCEPT', $http_accept, SQ_SERVER) &&
    !isset($attachment_common_types_parsed[$http_accept]) ) {
    attachment_common_parse($http_accept, $debug);
}

/* Complete autodetection of Javascript. */
$javascript_setting = getPref
    ($data_dir, $username, 'javascript_setting', SMPREF_JS_AUTODETECT);
$js_autodetect_results = (isset($js_autodetect_results) ?
    $js_autodetect_results : SMPREF_JS_OFF);
/* See if it's set to "Always on" */
$js_pref = SMPREF_JS_ON;
if ($javascript_setting != SMPREF_JS_ON){
    if ($javascript_setting == SMPREF_JS_AUTODETECT) {
        if ($js_autodetect_results == SMPREF_JS_OFF) {
            $js_pref = SMPREF_JS_OFF;
        }
    } else {
        $js_pref = SMPREF_JS_OFF;
    }
}
/* Update the prefs */
setPref($data_dir, $username, 'javascript_on', $js_pref);

/* Compute the URL to forward the user to. */
$redirect_url = 'webmail.php';

if ( sqgetGlobalVar('session_expired_location', $session_expired_location, SQ_SESSION) ) {
    sqsession_unregister('session_expired_location');
    $compose_new_win = getPref($data_dir, $username, 'compose_new_win', 0);
    if ($compose_new_win) {
        $redirect_url = $session_expired_location;
    } elseif ( strpos($session_expired_location, 'webmail.php') === FALSE ) {
        $redirect_url = 'webmail.php?right_frame='.urldecode($session_expired_location);
    }
    unset($session_expired_location);
}

/* Write session data and send them off to the appropriate page. */
session_write_close();
header("Location: $redirect_url");

/* --------------------- end main ----------------------- */

function attachment_common_parse($str, $debug) {
    global $attachment_common_types, $attachment_common_types_parsed;

    $attachment_common_types_parsed[$str] = true;
    
    /* 
     * Replace ", " with "," and explode on that as Mozilla 1.x seems to  
     * use "," to seperate whilst IE, and earlier versions of Mozilla use
     * ", " to seperate
     */
    
    $str = str_replace( ', ' , ',' , $str );
    $types = explode(',', $str);

    foreach ($types as $val) {
        // Ignore the ";q=1.0" stuff
        if (strpos($val, ';') !== false)
            $val = substr($val, 0, strpos($val, ';'));

        if (! isset($attachment_common_types[$val])) {
            $attachment_common_types[$val] = true;
        }
    }
    $_SESSION['attachment_common_types'] = $attachment_common_types;
}


?>
