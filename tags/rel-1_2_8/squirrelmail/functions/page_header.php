<?php

/**
 * page_header.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Prints the page header (duh)
 *
 * $Id$
 */

require_once('../functions/strings.php');
require_once('../functions/imap_utf7_decode_local.php');
require_once('../src/global.php');

/* Always set up the language before calling these functions */
function displayHtmlHeader( $title = 'SquirrelMail', $xtra = '', $do_hook = TRUE ) {
    if ( (float)substr(PHP_VERSION,0,3) < 4.1 ) {
            global $_SESSION;
    }
    if (isset($_SESSION['base_uri'])) {
        $base_uri = $_SESSION['base_uri'];
    }
    else {
        global $base_uri;
    }
    global $theme_css, $custom_css;

    echo '<!DOCTYPE HTML PUBLIC "-//W3C//DTD HTML 4.0 Transitional//EN">' .
         "\n\n<HTML>\n<HEAD>\n";

    if ( !isset( $custom_css ) || $custom_css == 'none' ) {
        if ($theme_css != '') {
            echo "<LINK REL=\"stylesheet\" TYPE=\"text/css\" HREF=\"$theme_css\">";
        }
    } else {
        echo '<LINK REL="stylesheet" TYPE="text/css" HREF="' .
             $base_uri . 'themes/css/'.$custom_css.'">';
    }
    
    if ($do_hook) {
        do_hook ("generic_header");
    }

    echo "\n<title>$title</title>$xtra</head>\n\n";

}

function displayInternalLink($path, $text, $target='') {

    if ( (float)substr(PHP_VERSION,0,3) < 4.1 ) {
            global $_SESSION;
    }

    $base_uri = $_SESSION['base_uri'];    
    if ($target != '') {
        $target = " target=\"$target\"";
    }

    echo '<a href="'.$base_uri.$path.'"'.$target.'>'.$text.'</a>';
}

function displayPageHeader($color, $mailbox, $xtra='', $session=false) {

    global $hide_sm_attributions, $PHP_SELF, $frame_top,
           $compose_new_win, $username, $datadir, $compose_width, $compose_height,
           $attachemessages, $session;
           
    if ( (float)substr(PHP_VERSION,0,3) < 4.1 ) {
            global $_SESSION;
    }

    $base_uri = $_SESSION['base_uri'];
    $delimiter = $_SESSION['delimiter'];
    $module = substr( $PHP_SELF, ( strlen( $PHP_SELF ) - strlen( $base_uri ) ) * -1 );
    if ($qmark = strpos($module, '?')) {
        $module = substr($module, 0, $qmark);
    }
    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    /*
        Locate the first displayable form element
    */

    if ($session != false) {
	$compose_uri = 'src/compose.php?mailbox='. urlencode($mailbox).'&attachedmessages=true&session='."$session";
    } else {
        $compose_uri = 'src/compose.php?newmessage=1';
	$session = 0;
    }
   
    switch ( $module ) {
    case 'src/read_body.php':
            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js = "\n".'<script language="JavaScript" type="text/javascript">' .
                    "\n<!--\n";
                $js .= "function comp_in_new(new_mes, comp_uri) {\n".
		     '    if (new_mes) { '."\n".
		     "       comp_uri = \"".$base_uri."src/compose.php?newmessage=1\";\n".
		     '    } else { '."\n".
		     "       if (comp_uri =='') {\n".
		     '           comp_uri = "'.$base_uri.$compose_uri."\";\n".
		     '       }'. "\n".
		     '    }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank", "width='.$compose_width.",height=$compose_height".
                     ",scrollbars=yes,resizable=yes\");\n".
                     "}\n";

        $js .= "// -->\n".
        	 "</script>\n";
        displayHtmlHeader ('Squirrelmail', $js);
            }
        displayHtmlHeader();
        $onload = $xtra;
        break;
    case 'src/compose.php':
        $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "function checkForm() {\n".
                "var f = document.forms.length;\n".
                "var i = 0;\n".
                "var pos = -1;\n".
                "while( pos == -1 && i < f ) {\n".
                    "var e = document.forms[i].elements.length;\n".
                    "var j = 0;\n".
                    "while( pos == -1 && j < e ) {\n".
                        "if ( document.forms[i].elements[j].type == 'text' ) {\n".
                            "pos = j;\n".
                        "}\n".
                        "j++;\n".
                    "}\n".
                "i++;\n".
                "}\n".
                "if( pos >= 0 ) {\n".
                    "document.forms[i-1].elements[pos].focus();\n".
                "}\n".
            "}\n";
	    
        $js .= "// -->\n".
        	 "</script>\n";
        $onload = "onLoad=\"checkForm();\"";
        displayHtmlHeader ('Squirrelmail', $js);
        break;   

    default:
        $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "function checkForm() {\n".
                "var f = document.forms.length;\n".
                "var i = 0;\n".
                "var pos = -1;\n".
                "while( pos == -1 && i < f ) {\n".
                    "var e = document.forms[i].elements.length;\n".
                    "var j = 0;\n".
                    "while( pos == -1 && j < e ) {\n".
                        "if ( document.forms[i].elements[j].type == 'text' ) {\n".
                            "pos = j;\n".
                        "}\n".
                        "j++;\n".
                    "}\n".
                "i++;\n".
                "}\n".
                "if( pos >= 0 ) {\n".
                    "document.forms[i-1].elements[pos].focus();\n".
                "}\n".
		"$xtra\n".
            "}\n";
	    
            if ($compose_new_win == '1') {
                if (!preg_match("/^[0-9]{3,4}$/", $compose_width)) {
                    $compose_width = '640';
                }
                if (!preg_match("/^[0-9]{3,4}$/", $compose_height)) {
                    $compose_height = '550';
                }
                $js .= "function comp_in_new(new_mes, comp_uri) {\n".
		     '    if (new_mes) { '."\n".
		     "       comp_uri = \"".$base_uri."src/compose.php?newmessage=1\";\n".
		     '    } else { '."\n".
		     "       if (comp_uri =='') {\n".
		     '           comp_uri = "'.$base_uri.$compose_uri."\";\n".
		     '       }'. "\n".
		     '    }'. "\n".
                     '    var newwin = window.open(comp_uri' .
                     ', "_blank","width='.$compose_width.",height=$compose_height".
                     ",scrollbars=yes,resizable=yes\");\n".
                     "}\n";
            }
        $js .= "// -->\n".
        	 "</script>\n";
        $onload = "onLoad=\"checkForm();\"";
        displayHtmlHeader ('Squirrelmail', $js);
        break;   

    }

    echo "<BODY TEXT=\"$color[8]\" BGCOLOR=\"$color[4]\" LINK=\"$color[7]\" VLINK=\"$color[7]\" ALINK=\"$color[7]\" $onload>\n\n";
    /** Here is the header and wrapping table **/
    $shortBoxName = imap_utf7_decode_local(
		      readShortMailboxName($mailbox, $delimiter));
    if ( $shortBoxName == 'INBOX' ) {
        $shortBoxName = _("INBOX");
    }
    echo "<A NAME=pagetop></A>\n"
        . "<TABLE BGCOLOR=\"$color[4]\" BORDER=0 WIDTH=\"100%\" CELLSPACING=0 CELLPADDING=2>\n"
        . "   <TR BGCOLOR=\"$color[9]\" >\n"
        . "      <TD ALIGN=left>\n";
    if ( $shortBoxName <> '' && strtolower( $shortBoxName ) <> 'none' ) {
        echo '         ' . _("Current Folder") . ": <B>$shortBoxName&nbsp;</B>\n";
    } else {
        echo '&nbsp;';
    }
    echo  "      </TD>\n"
        . '      <TD ALIGN=right><b>';
    displayInternalLink ('src/signout.php', _("Sign Out"), $frame_top);
    echo "</b></TD>\n"
        . "   </TR>\n"
        . "   <TR BGCOLOR=\"$color[4]\">\n"
        . "      <TD ALIGN=left>\n";
    $urlMailbox = urlencode($mailbox);
    if ($compose_new_win == '1') {
        echo "<a href=\"javascript:void(0)\" onclick=\"comp_in_new(true,'')\">". _("Compose"). '</a>';
    }
    else {
        displayInternalLink ("src/compose.php?mailbox=$urlMailbox", _("Compose"), 'right');
    } 
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/addressbook.php", _("Addresses"), 'right');
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/folders.php", _("Folders"), 'right');
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/options.php", _("Options"), 'right');
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/search.php?mailbox=$urlMailbox", _("Search"), 'right');
    echo "&nbsp;&nbsp;\n";
    displayInternalLink ("src/help.php", _("Help"), 'right');
    echo "&nbsp;&nbsp;\n";

    do_hook("menuline");

    echo "      </TD>\n      <TD ALIGN=\"right\">";
    echo ($hide_sm_attributions ? '&nbsp;' :
            '<A HREF="http://www.squirrelmail.org/" TARGET="_blank">SquirrelMail</A>');
    echo "</TD>\n".
        "   </TR>\n".
        "</TABLE>\n\n";
}

/* blatently copied/truncated/modified from the above function */
function compose_Header($color, $mailbox) {

    global $delimiter, $hide_sm_attributions, $base_uri, $PHP_SELF, $frame_top, $compose_new_win;


    $module = substr( $PHP_SELF, ( strlen( $PHP_SELF ) - strlen( $base_uri ) ) * -1 );
    if (!isset($frame_top)) {
        $frame_top = '_top';
    }

    /*
        Locate the first displayable form element
    */
    switch ( $module ) {
    case 'src/search.php':
        $pos = getPref($data_dir, $username, 'search_pos', 0 ) - 1;
        $onload = "onLoad=\"document.forms[$pos].elements[2].focus();\"";
        displayHtmlHeader (_("Compose"));
        break;
    default:
        $js = '<script language="JavaScript" type="text/javascript">' .
             "\n<!--\n" .
             "function checkForm() {\n".
                "var f = document.forms.length;\n".
                "var i = 0;\n".
                "var pos = -1;\n".
                "while( pos == -1 && i < f ) {\n".
                    "var e = document.forms[i].elements.length;\n".
                    "var j = 0;\n".
                    "while( pos == -1 && j < e ) {\n".
                        "if ( document.forms[i].elements[j].type == 'text' ) {\n".
                            "pos = j;\n".
                        "}\n".
                        "j++;\n".
                    "}\n".
                "i++;\n".
                "}\n".
                "if( pos >= 0 ) {\n".
                    "document.forms[i-1].elements[pos].focus();\n".
                "}\n".
            "}\n";
        $js .= "// -->\n".
        	 "</script>\n";
        $onload = "onLoad=\"checkForm();\"";
        displayHtmlHeader (_("Compose"), $js);
        break;   

    }

    echo "<BODY TEXT=\"$color[8]\" BGCOLOR=\"$color[4]\" LINK=\"$color[7]\" VLINK=\"$color[7]\" ALINK=\"$color[7]\" $onload>\n\n";
}
?>
