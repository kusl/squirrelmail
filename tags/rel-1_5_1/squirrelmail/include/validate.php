<?php

/**
 * validate.php
 *
 * @copyright &copy; 1999-2006 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 */

/** include the mime class before the session start ! otherwise we can't store
 * messages with a session_register.
 *
 * From http://www.php.net/manual/en/language.oop.serialization.php:
 *   In case this isn't clear:
 *   In 4.2 and below:
 *      session.auto_start and session objects are mutually exclusive.
 *
 * We need to load the classes before the session is started,
 * except that the session could be started automatically
 * via session.auto_start. So, we'll close the session,
 * then load the classes, and reopen the session which should
 * make everything happy.
 *
 * ** Note this means that for the 1.3.2 release, we should probably
 * recommend that people set session.auto_start=0 to avoid this altogether.
 */

session_write_close();

/**
 * Reset the $theme() array in case a value was passed via a cookie.
 * This is until theming is rewritten.
 */
global $theme;
unset($theme);
$theme=array();

/* SquirrelMail required files. */
include_once(SM_PATH . 'class/mime.class.php');
include_once(SM_PATH . 'functions/global.php');
include_once(SM_PATH . 'functions/strings.php');

/* set the name of the session cookie */
if(isset($session_name) && $session_name) {
    ini_set('session.name' , $session_name);
} else {
    ini_set('session.name' , 'SQMSESSID');
}

sqsession_is_active();

include_once(SM_PATH . 'functions/i18n.php');
include_once(SM_PATH . 'functions/auth.php');

is_logged_in();

/**
 * Auto-detection
 *
 * if $send (the form button's name) contains "\n" as the first char
 * and the script is compose.php, then trim everything. Otherwise, we
 * don't have to worry.
 *
 * This is for a RedHat package bug and a Konqueror (pre 2.1.1?) bug
 */
global $send, $PHP_SELF;
if (isset($send)
    && (substr($send, 0, 1) == "\n")
    && (substr($PHP_SELF, -12) == '/compose.php')) {
    if ($REQUEST_METHOD == 'POST') {
        global $HTTP_POST_VARS;
        TrimArray($HTTP_POST_VARS);
    } else {
        global $HTTP_GET_VARS;
        TrimArray($HTTP_GET_VARS);
    }
}

include_once(SM_PATH . 'functions/page_header.php');
include_once(SM_PATH . 'functions/prefs.php');
include_once(SM_PATH . 'config/config.php');
include_once(SM_PATH . 'include/load_prefs.php');

/* Set up the language (i18n.php was included by auth.php). */
global $username, $data_dir;
set_up_language(getPref($data_dir, $username, 'language'));

$timeZone = getPref($data_dir, $username, 'timezone');

/* Check to see if we are allowed to set the TZ environment variable.
 * We are able to do this if ...
 *   safe_mode is disabled OR
 *   safe_mode_allowed_env_vars is empty (you are allowed to set any) OR
 *   safe_mode_allowed_env_vars contains TZ
 */
$tzChangeAllowed = (!ini_get('safe_mode')) ||
                    !strcmp(ini_get('safe_mode_allowed_env_vars'),'') ||
                    preg_match('/^([\w_]+,)*TZ/', ini_get('safe_mode_allowed_env_vars'));

if ( $timeZone != SMPREF_NONE && ($timeZone != "")
    && $tzChangeAllowed ) {

    // get time zone key, if strict or custom strict timezones are used
    if (isset($time_zone_type) &&
        ($time_zone_type == 1 || $time_zone_type == 3)) {
        /* load time zone functions */
        require_once(SM_PATH . 'include/timezones.php');
        $realTimeZone = sq_get_tz_key($timeZone);
    } else {
        $realTimeZone = $timeZone;
    }

    // set time zone
    if ($realTimeZone) {
        putenv("TZ=".$realTimeZone);
    }
}

/* temporary sm_init section */

include_once(SM_PATH . 'class/template/template.class.php');
include_once(SM_PATH . 'class/error.class.php');
/*
 * Initialize the template object
 */
global $sTplDir;
$oTemplate = new Template($sTplDir);

/*
 * Initialize our custom error handler object
 */
$oErrorHandler = new ErrorHandler($oTemplate,'error_message.tpl');

/*
 * Activate custom error handling
 */
if (version_compare(PHP_VERSION, "4.3.0", ">=")) {
    $oldErrorHandler = set_error_handler(array($oErrorHandler, 'SquirrelMailErrorhandler'));
} else {
    $oldErrorHandler = set_error_handler('SquirrelMailErrorhandler');
}

?>