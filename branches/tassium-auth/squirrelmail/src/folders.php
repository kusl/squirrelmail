<?php

/**
 * folders.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Handles all interaction between the user and the other folder
 * scripts which do most of the work. Also handles the Special
 * Folders.
 *
 * $Id$
 */

/* Path for SquirrelMail required files. */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap_utf7_decode_local.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/array.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/html.php');

displayPageHeader($color, 'None');

/* get globals we may need */

$username = $_SESSION['username'];
$key = $_COOKIE['key'];
$delimiter = $_SESSION['delimiter'];
$onetimepad = $_SESSION['onetimepad'];
if (isset($_GET['success'])) {
    $success = $_GET['success'];
}

/* end of get globals */

echo '<br>' .
    html_tag( 'table', '', 'center', $color[0], 'width="95%" cellpadding="1" cellspacing="0" border="0"' ) .
        html_tag( 'tr' ) .
            html_tag( 'td', '', 'center' ) . '<b>' . _("Folders") . '</b>' .
                html_tag( 'table', '', 'center', '', 'width="100%" cellpadding="5" cellspacing="0" border="0"' ) .
                    html_tag( 'tr' ) .
                        html_tag( 'td', '', 'center', $color[4] );

if ( isset($success) && $success ) {

    $td_str = '<b>';

    switch ($success)
    {
        case 'subscribe':
            $td_str .=  _("Subscribed successfully!");
            break;
        case 'unsubscribe':
            $td_str .=  _("Unsubscribed successfully!");
            break;
        case 'delete':
            $td_str .=  _("Deleted folder successfully!");
            break;
        case 'create':
            $td_str .=  _("Created folder successfully!");
            break;
        case 'rename':
            $td_str .=  _("Renamed successfully!");
            break;
        case 'subscribe-doesnotexist':
            $td_str .=  _("Subscription Unsuccessful - Folder does not exist.");
            break;
    }

    $td_str .= '</b><br>';        


    echo html_tag( 'table',
                html_tag( 'tr',
                     html_tag( 'td', $td_str .
                               '<a href="../src/left_main.php" target=left>' .
                               _("refresh folder list") . '</a>' ,
                     'center' )
                ) ,
            'center', '', 'width="100%" cellpadding="4" cellspacing="0" border="0"' );
}

echo "\n<br>";

$imapConnection = sqimap_login ($username, $key, $imapServerAddress, $imapPort, 0);
$boxes = sqimap_mailbox_list($imapConnection);

/** CREATING FOLDERS **/
echo html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
            html_tag( 'tr',
                html_tag( 'td', '<b>' . _("Create Folder") . '</b>', 'center', $color[9] )
            ) .
            html_tag( 'tr' ) .
                html_tag( 'td', '', 'center', $color[0] ) .

     "<FORM NAME=cf ACTION=\"folders_create.php\" METHOD=\"POST\">\n".
     "<input type=TEXT SIZE=25 NAME=folder_name><BR>\n".
     _("as a subfolder of").
     '<BR>'.
     "<TT><SELECT NAME=subfolder>\n";
if ($default_sub_of_inbox == false) {
    echo '<OPTION SELECTED VALUE="">[ '._("None")." ]\n";
} else {
    echo '<OPTION VALUE="">[ '._("None")." ]\n";
}

for ($i = 0, $cnt=count($boxes); $i < $cnt; $i++) {
    if (!in_array('noinferiors', $boxes[$i]['flags'])) {
        if ((strtolower($boxes[$i]['unformatted']) == 'inbox') &&
            $default_sub_of_inbox) {

            $box = $boxes[$i]['unformatted'];
            $box2 = str_replace(' ', '&nbsp;',
                                imap_utf7_decode_local($boxes[$i]['unformatted-disp']));
            echo "<OPTION SELECTED VALUE=\"$box\">$box2</option>\n";
        } else {
            $box = $boxes[$i]['unformatted'];
            $box2 = str_replace(' ', '&nbsp;',
                                imap_utf7_decode_local($boxes[$i]['unformatted-disp'])); 
            if (strtolower($imap_server_type) != 'courier' ||
                  strtolower($box) != "inbox.trash")
                echo "<OPTION VALUE=\"$box\">$box2</option>\n";
        }
    }
}
echo "</SELECT></TT>\n";
if ($show_contain_subfolders_option) {
    echo '<br><input type=CHECKBOX NAME="contain_subs"> &nbsp;'
       . _("Let this folder contain subfolders")
       . '<BR>';
}
echo "<input type=SUBMIT VALUE=\""._("Create")."\">\n";
echo "</FORM></td></tr>\n";

echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."\n";

/** count special folders **/
$count_special_folders = 0;
$num_max = 1;
if (strtolower($imap_server_type) == "courier" || $move_to_trash) {
        $num_max++;
}
if ($move_to_sent) {
        $num_max++;
}
if ($save_as_draft) {
        $num_max++;
}
for ($p = 0, $cnt = count($boxes); $p < $cnt && $count_special_folders < $num_max; $p++) {
    if (strtolower($boxes[$p]['unformatted']) == 'inbox')
        $count_special_folders++;
    else if (strtolower($imap_server_type) == 'courier' &&
            strtolower($boxes[$p]['unformatted']) == 'inbox.trash')
        $count_special_folders++;
    else if ($boxes[$p]['unformatted'] == $trash_folder && $trash_folder)
        $count_special_folders++;
    else if ($boxes[$p]['unformatted'] == $sent_folder && $sent_folder)
        $count_special_folders++;
    else if ($boxes[$p]['unformatted'] == $draft_folder && $draft_folder)
        $count_special_folders++;
}


/** RENAMING FOLDERS **/
echo html_tag( 'tr',
            html_tag( 'td', '<b>' . _("Rename a Folder") . '</b>', 'center', $color[9] )
        ) .
        html_tag( 'tr' ) .
        html_tag( 'td', '', 'center', $color[0] );

if ($count_special_folders < count($boxes)) {
    echo "<FORM ACTION=\"folders_rename_getname.php\" METHOD=\"POST\">\n"
       . "<TT><SELECT NAME=old>\n"
       . '         <OPTION VALUE="">[ ' . _("Select a folder") . " ]</OPTION>\n";
    for ($i = 0; $i < count($boxes); $i++) {
        $use_folder = true;

        if ((strtolower($boxes[$i]['unformatted']) != 'inbox') &&
            ($boxes[$i]['unformatted'] != $trash_folder)  &&
            ($boxes[$i]['unformatted'] != $sent_folder) &&
            ($boxes[$i]['unformatted'] != $draft_folder)) {
            $box = $boxes[$i]['unformatted-dm'];

            $box2 = str_replace(' ', '&nbsp;',
                                imap_utf7_decode_local($boxes[$i]['unformatted-disp']));
            if (strtolower($imap_server_type) != 'courier' || strtolower($box) != 'inbox.trash') {
                echo "<OPTION VALUE=\"$box\">$box2</option>\n";
            }
        }
    }
    echo "</SELECT></TT>\n".
         "<input type=SUBMIT VALUE=\"".
         _("Rename").
         "\">\n".
         "</FORM></td></tr>\n";
} else {
    echo _("No folders found") . '<br><br></td></tr>';
}
$boxes_sub = $boxes;

echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."\n";

/** DELETING FOLDERS **/
echo html_tag( 'tr',
            html_tag( 'td', '<b>' . _("Delete Folder") . '</b>', 'center', $color[9] )
        ) .
        html_tag( 'tr' ) .
        html_tag( 'td', '', 'center', $color[0] );

if ($count_special_folders < count($boxes)) {
    echo "<FORM ACTION=\"folders_delete.php\" METHOD=\"POST\">\n"
       . "<TT><SELECT NAME=mailbox>\n"
       . '         <OPTION VALUE="">[ ' . _("Select a folder") . " ]</OPTION>\n";
    for ($i = 0; $i < count($boxes); $i++) {
        $use_folder = true;
        if ((strtolower($boxes[$i]['unformatted']) != 'inbox') &&
            ($boxes[$i]['unformatted'] != $trash_folder) &&
            ($boxes[$i]['unformatted'] != $sent_folder) &&
            ($boxes[$i]['unformatted'] != $draft_folder) &&
            (!in_array('noselect', $boxes[$i]['flags'])) &&
            ((strtolower($imap_server_type) != 'courier') ||
             (strtolower($boxes[$i]['unformatted']) != 'inbox.trash'))) {
            $box = $boxes[$i]['unformatted-dm'];
            $box2 = str_replace(' ', '&nbsp;',
                                imap_utf7_decode_local($boxes[$i]['unformatted-disp']));
            echo "         <OPTION VALUE=\"$box\">$box2</option>\n";
        }
    }
    echo "</SELECT></TT>\n"
       . '<input type=SUBMIT VALUE="'
       . _("Delete")
       . "\">\n"
       . "</form></td></tr>\n";
} else {
    echo _("No folders found") . "<br><br></td></tr>";
}

echo html_tag( 'tr',
            html_tag( 'td', '&nbsp;', 'left', $color[4] )
        ) ."</table>\n";


/** UNSUBSCRIBE FOLDERS **/
echo html_tag( 'table', '', 'center', '', 'width="70%" cellpadding="4" cellspacing="0" border="0"' ) .
            html_tag( 'tr',
                html_tag( 'td', '<b>' . _("Unsubscribe") . '/' . _("Subscribe") . '</b>', 'center', $color[9], 'colspan="2"' )
            ) .
            html_tag( 'tr' ) .
                html_tag( 'td', '', 'center', $color[0], 'width="50%"' );

if ($count_special_folders < count($boxes)) {
    echo "<FORM ACTION=\"folders_subscribe.php?method=unsub\" METHOD=\"POST\">\n"
       . "<TT><SELECT NAME=\"mailbox[]\" multiple size=8>\n";
    for ($i = 0; $i < count($boxes); $i++) {
        $use_folder = true;
        if ((strtolower($boxes[$i]["unformatted"]) != "inbox") &&
            ($boxes[$i]["unformatted"] != $trash_folder) &&
            ($boxes[$i]["unformatted"] != $sent_folder) &&
            ($boxes[$i]["unformatted"] != $draft_folder)) {
            $box = $boxes[$i]["unformatted-dm"];
            $box2 = str_replace(' ', '&nbsp;',
                                imap_utf7_decode_local($boxes[$i]["unformatted-disp"]));
            echo "         <OPTION VALUE=\"$box\">$box2\n";
        }
    }
    echo "</SELECT></TT><br><br>\n"
       . '<input type=SUBMIT VALUE="'
       . _("Unsubscribe")
       . "\">\n"
       . "</FORM></td>\n";
} else {
    echo _("No folders were found to unsubscribe from!") . '</td>';
}
$boxes_sub = $boxes;

/** SUBSCRIBE TO FOLDERS **/
echo html_tag( 'td', '', 'center', $color[0], 'width="50%"' );
if(!$no_list_for_subscribe) {
  $boxes_all = sqimap_mailbox_list_all ($imapConnection);

  $box = '';
  $box2 = '';
  for ($i = 0, $q = 0; $i < count($boxes_all); $i++) {
    $use_folder = true;
    for ($p = 0; $p < count ($boxes); $p++) {
        if ($boxes_all[$i]['unformatted'] == $boxes[$p]['unformatted']) {
            $use_folder = false;
            continue;
        } else if ($boxes_all[$i]['unformatted-dm'] == $folder_prefix) {
            $use_folder = false;
        }
    }
    if ($use_folder == true) {
        $box[$q] = $boxes_all[$i]['unformatted-dm'];
        $box2[$q] = imap_utf7_decode_local($boxes_all[$i]['unformatted-disp']);
        $q++;
    }
  }
  if ($box && $box2) {
    echo "<FORM ACTION=\"folders_subscribe.php?method=sub\" METHOD=\"POST\">\n"
       . '<tt><select name="mailbox[]" multiple size=8>';

    for ($q = 0; $q < count($box); $q++) {      
       echo "         <OPTION VALUE=\"$box[$q]\">".$box2[$q]."\n";
    }      
    echo '</select></tt><br><br>'
       . '<input type=SUBMIT VALUE="'. _("Subscribe") . "\">\n"
       . "</FORM></td></tr></table><BR>\n";
  } else {
    echo _("No folders were found to subscribe to!") . '</td></tr></table>';
  }
} else {
  /* don't perform the list action -- this is much faster */
  echo "<FORM ACTION=\"folders_subscribe.php?method=sub\" METHOD=\"POST\">\n"
     . _("Subscribe to:") . '<br>'
     . '<tt><input type="text" name="mailbox[]" size=35>'
     . '<INPUT TYPE=SUBMIT VALUE="'. _("Subscribe") . "\">\n"
     . "</FORM></TD></TR></TABLE><BR>\n";
}

do_hook('folders_bottom');
?>

    </td></tr>
    </table>

</td></tr>
</table>

<?php
   sqimap_logout($imapConnection);
?>

</body></html>
