<?php

/**
 * setup.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * Implementation of RFC 2369 for SquirrelMail.
 * When viewing a message from a mailinglist complying with this RFC,
 * this plugin displays a menu which gives the user a choice of mailinglist
 * commands such as (un)subscribe, help and list archives.
 *
 * $Id$
 */

function squirrelmail_plugin_init_listcommands () {
    global $squirrelmail_plugin_hooks;

    $squirrelmail_plugin_hooks['read_body_header']['listcommands'] = 'plugin_listcommands_menu';
}

function plugin_listcommands_menu() {
    global $imapConnection, $passed_id, $color, $mailbox,
           $message, $ent_num, $priority_level, $compose_new_win, $uid_support;

    $subject = trim($message->rfc822_header->subject);

    /**
     * Array of commands we can deal with from the header. The Reply option
     * is added later because we generate it using the Post information.
     */
    $fieldsdescr = array('Post'        => _("Post to List"),
                         'Reply'       => _("Reply to List"),
                         'Subscribe'   => _("Subscribe"),
                         'Unsubscribe' => _("Unsubscribe"),
                         'Archive'     => _("List Archives"),
                         'Owner'       => _("Contact Listowner"),
                         'Help'        => _("Help"));
    $fields = array_keys($fieldsdescr);

    $sorted_cmds = array();
    $unsorted_cmds = array();
    $output = array();

    $lfields = 'List-' . implode (' List-', $fields);

    $sid = sqimap_session_id($uid_support);
    fputs ($imapConnection, "$sid FETCH $passed_id BODY.PEEK[HEADER.FIELDS ($lfields)]\r\n");
    $read = sqimap_read_data($imapConnection, $sid, true, $response, $emessage);

    for ($i = 1; $i < count($read); $i++) {
        foreach ($fields as $field) {
            if ( preg_match("/^List-$field: *<(.+?)>/i", $read[$i], $match) ) {
                $unsorted_cmds[$field] = $match[1];
            }
        }
    }

    if (count($unsorted_cmds) == 0) {
        return;
    }

    foreach ($fields as $field) {
        foreach ($unsorted_cmds as $cmd => $url) {
            if ($field == $cmd) {
                $cmds[$cmd] = $url;
            }
        }
    }

    foreach ($cmds as $cmd => $url) {
        if (eregi('mailto:(.+)', $url, $regs)) {
            $purl = parse_url($url);

            if (($cmd == 'Post') || ($cmd == 'Owner')) {
                $url = 'compose.php?';
            } else {
                $url = "../plugins/listcommands/mailout.php?action=$cmd&amp;";
		
            }
            $url .= '&amp;send_to=' . $purl['path'];

            if (isset($purl['query'])) {
                $url .= '&amp;' . $purl['query'];
            }
            if ($compose_new_win == '1') {
                $output[] = "<a href=\"javascript:void(0)\" onclick=\"comp_in_new('$url')\">" . $fieldsdescr[$cmd] . '</A>';
            }
            else {
                $output[] = '<A HREF="' . $url . '">' . $fieldsdescr[$cmd] . '</A>';
            }
            if ($cmd == 'Post') {
	        $url .= '&amp;passed_id='.$passed_id.
		        '&amp;mailbox='.urlencode($mailbox).
		        (isset($passed_ent_id)?'&amp;passed_ent_id='.$passed_ent_id:'');
                $url .= '&amp;action=reply';
                if ($compose_new_win == '1') {
                    $output[] = "<A HREF=\"javascript:void(0)\" onClick=\"comp_in_new('$url')\">" . $fieldsdescr['Reply'] . '</A>';
                }
                else {
                    $output[] = '<A HREF="' . $url . '">' . $fieldsdescr['Reply'] . '</A>';
                }
            }
        } else if (eregi('^(http|ftp)', $url)) {
            $output[] = '<A HREF="' . $url . '" TARGET="_blank">'
                      . $fieldsdescr[$cmd] . '</A>';
        }
    }

    if (count($output) > 0) {
        echo '<table width="100%" cellpadding="3" cellspacing="0" align="center"'.
             ' border="0" bgcolor="'.$color[0].'">'. "\n";
        echo '<tr>';
        echo html_tag( 'td', '<b>'._("Mailing List").':&nbsp;&nbsp;</b>', 'right', $color[0], 'valign="top" width="20%"') . "\n";
        echo html_tag( 'td', 
	         '<small>' . implode('&nbsp;|&nbsp;', $output) . '</small>', 'left', $color[0], 'valign="top" width="80%"');
        echo "\n</tr>";
	echo '</table>'."\n";
    }
}

?>
