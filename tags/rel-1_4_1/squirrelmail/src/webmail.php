<?php

/**
 * webmail.php -- Displays the main frameset
 *
 * Copyright (c) 1999-2003 The SquirrelMail development team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file generates the main frameset. The files that are
 * shown can be given as parameters. If the user is not logged in
 * this file will verify username and password.
 *
 * $Id$
 */

/* Path for SquirrelMail required files. */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'config/config.php');
require_once(SM_PATH . 'functions/prefs.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/i18n.php');
require_once(SM_PATH . 'functions/auth.php');
require_once(SM_PATH . 'functions/global.php');

if (!function_exists('sqm_baseuri')){
    require_once(SM_PATH . 'functions/display_messages.php');
}
$base_uri = sqm_baseuri();

session_start();

sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

sqgetGlobalVar('right_frame', $right_frame, SQ_GET);

if ( isset($_SESSION['session_expired_post']) ) {
    sqsession_unregister('session_expired_post');
}

is_logged_in();

do_hook('webmail_top');

/**
 * We'll need this to later have a noframes version
 *
 * Check if the user has a language preference, but no cookie.
 * Send him a cookie with his language preference, if there is
 * such discrepancy.
 */
$my_language = getPref($data_dir, $username, 'language');
if ($my_language != $squirrelmail_language) {
    setcookie('squirrelmail_language', $my_language, time()+2592000, $base_uri);
}

set_up_language(getPref($data_dir, $username, 'language'));

echo "<!DOCTYPE HTML PUBLIC \"-//W3C//DTD HTML 4.01 Frameset//EN\">\n".
     "<html><head>\n" .
     "<title>$org_title</title>\n".
     "</head>";

$left_size = getPref($data_dir, $username, 'left_size');
$location_of_bar = getPref($data_dir, $username, 'location_of_bar');

if (isset($languages[$squirrelmail_language]['DIR']) &&
    strtolower($languages[$squirrelmail_language]['DIR']) == 'rtl') {
    $temp_location_of_bar = 'right';
} else {
    $temp_location_of_bar = 'left';
}

if ($location_of_bar == '') {
    $location_of_bar = $temp_location_of_bar;
}
$temp_location_of_bar = '';

if ($left_size == "") {
    if (isset($default_left_size)) {
         $left_size = $default_left_size;
    }
    else {
        $left_size = 200;
    }
}

if ($location_of_bar == 'right') {
    echo "<frameset cols=\"*, $left_size\" id=\"fs1\">\n";
}
else {
    echo "<frameset cols=\"$left_size, *\" id=\"fs1\">\n";
}

/*
 * There are three ways to call webmail.php
 * 1.  webmail.php
 *      - This just loads the default entry screen.
 * 2.  webmail.php?right_frame=right_main.php&sort=X&startMessage=X&mailbox=XXXX
 *      - This loads the frames starting at the given values.
 * 3.  webmail.php?right_frame=folders.php
 *      - Loads the frames with the Folder options in the right frame.
 *
 * This was done to create a pure HTML way of refreshing the folder list since
 * we would like to use as little Javascript as possible.
 */
if (!isset($right_frame)) {
    $right_frame = '';
} 
if ($right_frame == 'right_main.php') {
    $urlMailbox = urlencode($mailbox);
    $right_frame_url =
        "right_main.php?mailbox=$urlMailbox&amp;sort=$sort&amp;startMessage=$startMessage";
} elseif ($right_frame == 'options.php') {
    $right_frame_url = 'options.php';
} elseif ($right_frame == 'folders.php') {
    $right_frame_url = 'folders.php';
} else if ($right_frame == '') {
    $right_frame_url = 'right_main.php';
} else {
    $right_frame_url =  $right_frame;
}

if ($location_of_bar == 'right') {
    echo "<frame src=\"$right_frame_url\" name=\"right\" frameborder=\"1\" />\n" .
         "<frame src=\"left_main.php\" name=\"left\" frameborder=\"1\" />\n";
}
else {
    echo "<frame src=\"left_main.php\" name=\"left\" frameborder=\"1\" />\n".
         "<frame src=\"$right_frame_url\" name=\"right\" frameborder=\"1\" />\n";
}
do_hook('webmail_bottom');
?>
</frameset>
</html>
