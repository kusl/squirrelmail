<?php

/**
 * login.php -- simple login screen
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This a simple login screen. Some housekeeping is done to clean
 * cookies and find language.
 *
 * $Id$
 */

if (isset($_GET['emailaddress'])) {
    $emailaddress = $_GET['emailaddress'];
}

$rcptaddress = '';
if (isset($emailaddress)) {
    if (stristr($emailaddress, 'mailto:')) {
        $rcptaddress = substr($emailaddress, 7);
    } else {
        $rcptaddress = $emailaddress;
    }
    
    if (($pos = strpos($rcptaddress, '?')) !== false) {
        $a = substr($rcptaddress, $pos + 1);
        $rcptaddress = substr($rcptaddress, 0, $pos);
        $a = explode('=', $a, 2);
        if (isset($a[1])) {
            $name = $a[0];
            $val = $a[1];
            global $$name;
            $$name = $val;
        }
    }
    
    /* At this point, we have parsed a lot of the mailto stuff. */
    /*   Let's do the rest -- CC, BCC, Subject, Body            */
    /*   Note:  They can all be case insensitive                */
    foreach ($GLOBALS as $k => $v) {
        $key = strtolower($k);
        $value = urlencode($v);
        if ($key == 'cc') {
            $rcptaddress .= '&amp;send_to_cc=' . $value;
        } else if ($key == 'bcc') {
            $rcptaddress .= '&amp;send_to_bcc=' . $value;
        } else if ($key == 'subject') {
            $rcptaddress .= '&amp;subject=' . $value;
        } else if ($key == 'body') {
            $rcptaddress .= '&amp;body=' . $value;
        }
    }
    
    /* Double-encode in this fashion to get past redirect.php properly. */
    $rcptaddress = urlencode($rcptaddress);
}

require_once('../functions/strings.php');
require_once('../config/config.php');
require_once('../src/global.php');
require_once('../functions/i18n.php');
require_once('../functions/plugin.php');
require_once('../functions/constants.php');
require_once('../functions/page_header.php');

/*
 * $squirrelmail_language is set by a cookie when the user selects
 * language and logs out
 */
if (isset($_COOKIE['squirrelmail_language'])) {
        $squirrelmail_language = $_COOKIE['squirrelmail_language'];
}
set_up_language($squirrelmail_language, TRUE);

/**
 * Find out the base URI to set cookies.
 */
if (!function_exists('sqm_baseuri')){
    require_once('../functions/display_messages.php');
}
$base_uri = sqm_baseuri();

/*
 * In case the last session was not terminated properly, make sure
 * we get a new one.
 */
 
sqsession_destroy();
header('Pragma: no-cache');

do_hook('login_cookie');

/* Output the javascript onload function. */

$header = "<SCRIPT LANGUAGE=\"JavaScript\" TYPE=\"text/javascript\">\n" .
          "<!--\n".
          "  function squirrelmail_loginpage_onload() {\n".
          "    document.forms[0].js_autodetect_results.value = '" . SMPREF_JS_ON . "';\n".
          '    document.forms[0].elements[' . (isset($loginname) ? 1 : 0) . "].focus();\n".
          "  }\n".
          "// -->\n".
          "</script>\n";
$custom_css = 'none';          
displayHtmlHeader( "$org_name - " . _("Login"), $header, FALSE );

/* Set the title of this page. */
echo '<BODY TEXT="#000000" BGCOLOR="#FFFFFF" LINK="#0000CC" VLINK="#0000CC" ALINK="#0000CC" onLoad="squirrelmail_loginpage_onload();">'.
     "\n<FORM ACTION=\"redirect.php\" METHOD=\"POST\">\n";

$username_form_name = 'login_username';
$password_form_name = 'secretkey';
do_hook('login_top');

$loginname_value = (isset($loginname) ? htmlspecialchars($loginname) : '');

/* Display width and height like good little people */
$width_and_height = '';
if (isset($org_logo_width) && is_numeric($org_logo_width) && $org_logo_width>0) {
    $width_and_height = " WIDTH=\"$org_logo_width\"";
}
if (isset($org_logo_height) && is_numeric($org_logo_height) && $org_logo_height>0) {
    $width_and_height .= " HEIGHT=\"$org_logo_height\"";
}

echo '<CENTER>'.
     "  <IMG SRC=\"$org_logo\" ALT=\"" . sprintf(_("%s Logo"), $org_name) . 
        "\"$width_and_height><BR>\n".
     ( $hide_sm_attributions ? '' :
       '<SMALL>' . sprintf (_("SquirrelMail version %s"), $version) . "<BR>\n".
       '  ' . _("By the SquirrelMail Development Team") . "<BR></SMALL>\n" ) .
     "</CENTER>\n".

     "<CENTER>\n".
     "<TABLE COLS=\"1\" WIDTH=\"350\">\n".
     "   <TR><TD ALIGN=CENTER BGCOLOR=\"#DCDCDC\">\n".
     '      <B>' . sprintf (_("%s Login"), $org_name) . "</B>\n".
     "   </TD></TR>".
     "   <TR><TD BGCOLOR=\"#FFFFFF\"><TABLE COLS=2 WIDTH=\"100%\">\n".
     "      <TR>\n".
     '         <TD WIDTH="30%" ALIGN=right>' . _("Name:") . "</TD>\n".
     "         <TD WIDTH=\"*\" ALIGN=left>\n".
     "            <INPUT TYPE=TEXT NAME=\"$username_form_name\" VALUE=\"$loginname_value\">\n".
     "         </TD>\n".
     "      </TR>\n".
     "      <TR>\n".
     '         <TD WIDTH="30%" ALIGN=right>' . _("Password:") . "</TD>\n".
     "         <TD WIDTH=\"*\" ALIGN=left>\n".
     "            <INPUT TYPE=PASSWORD NAME=\"$password_form_name\">\n".
     "            <INPUT TYPE=HIDDEN NAME=\"js_autodetect_results\" VALUE=\"" . SMPREF_JS_OFF . "\">\n".
     "            <INPUT TYPE=HIDDEN NAME=\"just_logged_in\" value=1>\n";
if ($rcptaddress != '') {
    echo "         <INPUT TYPE=HIDDEN NAME=\"rcptemail\" VALUE=\"".htmlspecialchars($rcptaddress)."\">\n";
}
echo "         </TD>\n".
     "      </TR>\n".
     "   </TABLE></TD></TR>\n".
     "   <TR><TD>\n".
     '      <CENTER><INPUT TYPE=SUBMIT VALUE="' . _("Login") . "\"></CENTER>\n".
     "   </TD></TR>\n".
     "</TABLE>\n".
     "</CENTER>\n";

do_hook('login_form');
echo "</FORM>\n";

do_hook('login_bottom');
echo "</BODY>\n".
     "</HTML>\n";
?>
