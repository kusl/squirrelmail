<?php

/**
 * login.php -- simple login screen
 *
 * This a simple login screen. Some housekeeping is done to clean
 * cookies and find language.
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
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'config/config.php');
require_once(SM_PATH . 'functions/i18n.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/constants.php');
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/global.php');
require_once(SM_PATH . 'functions/imap_general.php');
require_once(SM_PATH . 'functions/forms.php');

/**
 * $squirrelmail_language is set by a cookie when the user selects
 * language and logs out
 */
set_up_language($squirrelmail_language, TRUE, TRUE);

/**
 * Find out the base URI to set cookies.
 */
if (!function_exists('sqm_baseuri')){
    require_once(SM_PATH . 'functions/display_messages.php');
}
$base_uri = sqm_baseuri();

/*
 * In case the last session was not terminated properly, make sure
 * we get a new one.
 */

sqsession_destroy();
/**
 * PHP bug. http://bugs.php.net/11643 (warning, spammed bug tracker) and
 * http://bugs.php.net/13834
 * SID constant is not destroyed in PHP 4.1.2, 4.2.3 and maybe other 
 * versions. Produces warning on login page. Bug should be fixed only in 4.3.0
 */
@sqsession_start();
header('Pragma: no-cache');

/**
 * This detects if the IMAP server has logins disabled, and if so,
 * squelches the display of the login form and puts up a message
 * explaining the situation.
 */
if($imap_auth_mech == 'login') {
    /**
     * detect disabled login, only when imapServerAddress contains
     * server address and not mapping. See sqimap_get_user_server()
     */
    if (substr($imapServerAddress, 0, 4) != "map:") {
        $imap = sqimap_create_stream($imapServerAddress, $imapPort, $use_imap_tls);
        $logindisabled = sqimap_capability($imap,'LOGINDISABLED');
        sqimap_logout($imap);
        if ($logindisabled) {
            $string = _("The IMAP server is reporting that plain text logins are disabled.").'<br />'.
                _("Using CRAM-MD5 or DIGEST-MD5 authentication instead may work.").'<br />';
            if (!$use_imap_tls) {
                $string .= _("Also, the use of TLS may allow SquirrelMail to login.").'<br />';
            }
            $string .= _("Please contact your system administrator and report this error.");
            error_box($string,$color);
            exit;
        }
    }
}

/*
 * Initialize the template object and custom error handler object
 */
include_once(SM_PATH . 'class/template/template.class.php');
include_once(SM_PATH . 'class/error.class.php');

/*
 * $sTplDir is not initialized when a user is not logged in, so we will use
 * the config file defaults here.  If the neccesary variables are net set,
 * force a default value.
 */
$aTemplateSet = ( !isset($aTemplateSet) ? array() : $aTemplateSet );
$templateset_default = ( !isset($templateset_default) ? 0 : $templateset_default );
$sTplDir = ( !isset($aTemplateSet[$templateset_default]['PATH']) ? 
             SM_PATH . 'templates/default/' : 
             $aTemplateSet[$templateset_default]['PATH'] );

$oTemplate = new Template($sTplDir);
$oErrorHandler = new ErrorHandler($oTemplate,'error_message.tpl');

do_hook('login_cookie');

$loginname_value = (sqGetGlobalVar('loginname', $loginname) ? htmlspecialchars($loginname) : '');

/* Output the javascript onload function. */
$header = "<script type=\"text/javascript\">\n" .
          "<!--\n".
          "  function squirrelmail_loginpage_onload() {\n".
          "    var textElements = 0;\n".
          "    for (i = 0; i < document.forms[0].elements.length; i++) {\n".
          "      if (document.forms[0].elements[i].type == \"text\" || document.forms[0].elements[i].type == \"password\") {\n".
          "        textElements++;\n".
          "        if (textElements == " . (isset($loginname) ? 2 : 1) . ") {\n".
          "          document.forms[0].elements[i].focus();\n".
          "          break;\n".
          "        }\n".
          "      }\n".
          "    }\n".
          "  }\n".
          "// -->\n".
          "</script>\n";

if (@file_exists($theme[$theme_default]['PATH']))
   @include ($theme[$theme_default]['PATH']);

if (! isset($color) || ! is_array($color)) {
    // Add default color theme, if theme loading fails
    $color = array();
    $color[0]  = '#dcdcdc';  /* light gray    TitleBar               */
    $color[1]  = '#800000';  /* red                                  */
    $color[2]  = '#cc0000';  /* light red     Warning/Error Messages */
    $color[4]  = '#ffffff';  /* white         Normal Background      */
    $color[7]  = '#0000cc';  /* blue          Links                  */
    $color[8]  = '#000000';  /* black         Normal text            */
}

displayHtmlHeader( "$org_name - " . _("Login"), $header, FALSE );


/* If they don't have a logo, don't bother.. */
$logo_str = '';
if (isset($org_logo) && $org_logo) {
    /* Display width and height like good little people */
    $width_and_height = '';
    if (isset($org_logo_width) && is_numeric($org_logo_width) &&
     $org_logo_width>0) {
        $width_and_height = " width=\"$org_logo_width\"";
    }
    if (isset($org_logo_height) && is_numeric($org_logo_height) &&
     $org_logo_height>0) {
        $width_and_height .= " height=\"$org_logo_height\"";
    }
    
    $logo_str = '<img src="'.$org_logo.'" ' .
    			'alt="'. sprintf(_("%s Logo"), $org_name).'" ' .
    			$width_and_height .
                'class="sqm_loginImage" ' .
    			' /><br />'."\n";
}

$sm_attribute_str = '';
if (isset($hide_sm_attributions) && !$hide_sm_attributions) {
    $sm_attribute_str = _("SquirrelMail Webmail Application")."<br />\n" .
                        _("By the SquirrelMail Project Team")."<br />\n";
}

$username_form_name = 'login_username';
$password_form_name = 'secretkey';

if(sqgetGlobalVar('mailto', $mailto)) {
    $rcptaddress = addHidden('mailto', $mailto);
} else {
    $rcptaddress = '';
}

$password_field = addPwField($password_form_name).
                  addHidden('js_autodetect_results', SMPREF_JS_OFF).
                  $rcptaddress .
                  addHidden('just_logged_in', '1');

$oTemplate->assign('color', $color);
$oTemplate->assign('logo_str', $logo_str);
$oTemplate->assign('sm_attribute_str', $sm_attribute_str);
$oTemplate->assign('org_name_str', sprintf (_("%s Login"), $org_name));
$oTemplate->assign('login_field', addInput($username_form_name, $loginname_value));
$oTemplate->assign('password_field', $password_field);
$oTemplate->assign('submit_field', addSubmit(_("Login")));

$oTemplate->display('login.tpl');
?>