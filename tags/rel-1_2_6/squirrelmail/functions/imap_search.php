<?php

/**
 * imap_search.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * IMAP search routines
 *
 * $Id$
 */

require_once('../functions/imap.php');
require_once('../functions/date.php');
require_once('../functions/array.php');
require_once('../functions/mailbox_display.php');
require_once('../functions/mime.php');

function sqimap_search($imapConnection, $search_where, $search_what, $mailbox,
                       $color, $search_position = '', $search_all, $count_all) {

    global $msgs, $message_highlight_list, $squirrelmail_language, $languages,
           $index_order, $pos;

    $pos = $search_position;

    $urlMailbox = urlencode($mailbox);

    /* construct the search query, taking multiple search terms into account */
    $multi_search = array();
    $search_what  = trim($search_what);
    $search_what  = ereg_replace('[ ]{2,}', ' ', $search_what);
    $multi_search = explode(' ', $search_what);
    $search_string = '';
    foreach ($multi_search as $multi_search_part) {
        $search_string .= $search_where . ' {' . strlen($multi_search_part)
            . "}\r\n" . $multi_search_part . ' ';
    }

    $search_string = trim($search_string);

    /* now use $search_string in the imap search */
    if (isset($languages[$squirrelmail_language]['CHARSET']) &&
        $languages[$squirrelmail_language]['CHARSET']) {
        $ss = "SEARCH CHARSET "
            . strtoupper($languages[$squirrelmail_language]['CHARSET']) 
            . " ALL $search_string";
    } else {
        $ss = "SEARCH ALL $search_string";
    }

    /* read data back from IMAP */
    $readin = sqimap_run_command($imapConnection, $ss, true, $result, $message);

    /* try US-ASCII charset if search fails */
    if (isset($languages[$squirrelmail_language]['CHARSET']) 
        && strtolower($result) == 'no') {
        $ss = "SEARCH CHARSET \"US-ASCII\" ALL $search_string";
        $readin = sqimap_run_command ($imapConnection, $ss, true, 
                                      $result, $message);
    }

    unset($messagelist);
    $msgs = '';

    /* Keep going till we find the SEARCH response */
    foreach ($readin as $readin_part) {
        /* Check to see if a SEARCH response was received */
        if (substr($readin_part, 0, 9) == '* SEARCH ') {
            $messagelist = explode(' ', substr($readin_part, 9));
        } else if (isset($errors)) {
            $errors = $errors.$readin_part;
        } else {
            $errors = $readin_part;
        }
    }

    /* If nothing is found * SEARCH should be the first error else echo errors */
    if (isset($errors)) {
        if (strstr($errors,'* SEARCH')) {
            if ($search_all != 'all') {
                echo '<br><CENTER>' . _("No Messages Found") . '</CENTER>';
            }
            return;
        }
        echo "<!-- $errors -->";
    }

    /*
     * HACKED CODE FROM ANOTHER FUNCTION, could probably dump this and modify
     * existing code with a search true/false variable.
     */

    global $sent_folder;
    for ($q = 0; $q < count($messagelist); $q++) {
        $id[$q] = trim($messagelist[$q]);
    }
    $issent = ($mailbox == $sent_folder);
    $hdr_list = sqimap_get_small_header_list($imapConnection, $id, $issent);
    $flags = sqimap_get_flags_list($imapConnection, $id, $issent);
    foreach ($hdr_list as $hdr) {
        $from[] = $hdr->from;
        $date[] = $hdr->date;
        $subject[] = $hdr->subject;
        $to[] = $hdr->to;
        $priority[] = $hdr->priority;
        $cc[] = $hdr->cc;
        $size[] = $hdr->size;
        $type[] = $hdr->type0;
    }

    $j = 0;
    while ($j < count($messagelist)) {
            $date[$j] = str_replace('  ', ' ', $date[$j]);
            $tmpdate = explode(' ', trim($date[$j]));

            $messages[$j]["TIME_STAMP"] = getTimeStamp($tmpdate);
            $messages[$j]["DATE_STRING"] = getDateString($messages[$j]["TIME_STAMP"]);
            $messages[$j]["ID"] = $id[$j];
            $messages[$j]["FROM"] = decodeHeader($from[$j]);
            $messages[$j]["FROM-SORT"] = strtolower(sqimap_find_displayable_name(decodeHeader($from[$j])));
            $messages[$j]["SUBJECT"] = decodeHeader($subject[$j]);
            $messages[$j]["SUBJECT-SORT"] = strtolower(decodeHeader($subject[$j]));
            $messages[$j]["TO"] = decodeHeader($to[$j]);
            $messages[$j]["PRIORITY"] = $priority[$j];
            $messages[$j]["CC"] = $cc[$j];
            $messages[$j]["SIZE"] = $size[$j];
            $messages[$j]["TYPE0"] = $type[$j];

            $num = 0;
            while ($num < count($flags[$j])) {
                if ($flags[$j][$num] == 'Deleted') {
                    $messages[$j]['FLAG_DELETED'] = true;
                } else if ($flags[$j][$num] == 'Answered') {
                    $messages[$j]['FLAG_ANSWERED'] = true;
                } else if ($flags[$j][$num] == 'Seen') {
                    $messages[$j]['FLAG_SEEN'] = true;
                } else if ($flags[$j][$num] == 'Flagged') {
                    $messages[$j]['FLAG_FLAGGED'] = true;
                }
                $num++;
            }
            $j++;
    }

    /* Find and remove the ones that are deleted */
    $i = 0;
    $j = 0;
    while ($j < count($messagelist)) {
        if (isset($messages[$j]['FLAG_DELETED']) && $messages[$j]['FLAG_DELETED']) {
            $j++;
            continue;
        }
        $msgs[$i] = $messages[$j];

        $i++;
        $j++;
    }
    $numMessages = $i;

    /* There's gotta be messages in the array for it to sort them. */

    if (count($messagelist) > 0) {
        $j=0;
        if (!isset ($msg)) {
            $msg = '';
        }
        if ($search_all != 'all') {
            if ( !isset( $start_msg ) ) {
                $start_msg =0;
            }
            if ( !isset( $sort ) ) {
                    $sort = 0;
            }
            mail_message_listing_beginning( $imapConnection,
                "move_messages.php?msg=$msg&mailbox=$urlMailbox&pos=$pos&where=" . urlencode($search_where) . "&what=".urlencode($search_what),
                $mailbox,
                -1,
                '<b>' . _("Found") . ' ' . count($messagelist) . ' ' . _("messages") . '</b></tr><tr>'.
                get_selectall_link($start_msg, $sort));
        }
        else {
            mail_message_listing_beginning( $imapConnection,
                "move_messages.php?msg=$msg&mailbox=$urlMailbox&pos=$pos&where=" . urlencode($search_where) . "&what=".urlencode($search_what),
                $mailbox,
                -1,
                '<b>' . _("Found") . ' ' . count($messagelist) . ' ' . _("messages") . '</b></tr><tr>');
        }
        if ( $mailbox == 'INBOX' ) {
            $showbox = _("INBOX");
        } else {
            $showbox = $mailbox;
        }
        echo '<b><big>' . _("Folder:") . " $showbox</big></b>";
        while ($j < count($msgs)) {
            printMessageInfo($imapConnection, $msgs[$j]["ID"], 0, $j, $mailbox, '', 0, $search_where, $search_what);
            $j++;
            echo '</td></tr>';
        }
        echo '</table></td></tr></table></form>';
        $count_all = count($msgs);
    }
    return $count_all;
}

?>
