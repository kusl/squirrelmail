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
           $index_order, $pos, $allow_charset_search, $imap_server_type;

    $pos = $search_position;

    $urlMailbox = urlencode($mailbox);

    /* construct the search query, taking multiple search terms into account */
    $multi_search = array();
    $search_what  = trim($search_what);
    $search_what  = ereg_replace('[ ]{2,}', ' ', $search_what);
    $multi_search = explode(' ', $search_what);
    $search_string = '';

    /* it seems macosx does not support the prefered search 
       syntax so we fall back to the older style. This IMAP
       server has a problem with multiple search terms. Instead
       of returning the messages that match all the terms it
       returns the messages that match each term. Could be fixed
       on the client side, but should be fixed on the server
       as per the RFC */

    if ($imap_server_type == 'macosx') {
        foreach ($multi_search as $multi_search_part) {
            $search_string .= $search_where . ' ' .$multi_search_part . ' ';
        }
    }
    else {
        foreach ($multi_search as $multi_search_part) {
            $search_string .= $search_where . ' {' . strlen($multi_search_part)
                . "}\r\n" . $multi_search_part . ' ';
        }
    }

    $search_string = trim($search_string);

    /* now use $search_string in the imap search */
    if ($allow_charset_search && isset($languages[$squirrelmail_language]['CHARSET']) &&
        $languages[$squirrelmail_language]['CHARSET']) {
        $ss = "SEARCH CHARSET "
            . strtoupper($languages[$squirrelmail_language]['CHARSET']) 
            . " ALL $search_string";
    } else {
        $ss = "SEARCH ALL $search_string";
    }

    /* read data back from IMAP */
    $readin = sqimap_run_command($imapConnection, $ss, false, $result, $message);

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
        echo '<!-- '.htmlspecialchars($errors) .' -->';
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
//    $flags = sqimap_get_flags_list($imapConnection, $id, $issent);

    foreach ($hdr_list as $hdr) {
        $from[] = $hdr->from;
        $date[] = $hdr->date;
        $subject[] = $hdr->subject;
        $to[] = $hdr->to;
        $priority[] = $hdr->priority;
        $cc[] = $hdr->cc;
        $size[] = $hdr->size;
        $type[] = $hdr->type0;
	$flag_deleted[] = $hdr->flag_deleted;
	$flag_answered[] = $hdr->flag_answered;
	$flag_seen[] = $hdr->flag_seen;
	$flag_flagged[] = $hdr->flag_flagged;
    }

    $j = 0;
    while ($j < count($messagelist)) {
            $date[$j] = str_replace('  ', ' ', $date[$j]);
            $tmpdate = explode(' ', trim($date[$j]));

            $messages[$j]['TIME_STAMP'] = getTimeStamp($tmpdate);
            $messages[$j]['DATE_STRING'] = getDateString($messages[$j]['TIME_STAMP']);
            $messages[$j]['ID'] = $id[$j];
            $messages[$j]['FROM'] = decodeHeader($from[$j]);
            $messages[$j]['FROM-SORT'] = strtolower(sqimap_find_displayable_name(decodeHeader($from[$j])));
            $messages[$j]['SUBJECT'] = decodeHeader($subject[$j]);
            $messages[$j]['SUBJECT-SORT'] = strtolower(decodeHeader($subject[$j]));
            $messages[$j]['TO'] = decodeHeader($to[$j]);
            $messages[$j]['PRIORITY'] = $priority[$j];
            $messages[$j]['CC'] = $cc[$j];
            $messages[$j]['SIZE'] = $size[$j];
            $messages[$j]['TYPE0'] = $type[$j];
    	    $messages[$j]['FLAG_DELETED'] = $flag_deleted[$j];
            $messages[$j]['FLAG_ANSWERED'] = $flag_answered[$j];
            $messages[$j]['FLAG_SEEN'] = $flag_seen[$j];
            $messages[$j]['FLAG_FLAGGED'] = $flag_flagged[$j];
            $j++;

    }

    /* we used to skip deleted messages but now we don't :) */
    $j =0;
    $i =0;
    while ($j < count($messagelist)) {
            $msgs[$i] = $messages[$j];
        $i++;
        $j++;
    }

    $numMessages = $i;
    /* There's gotta be messages in the array for it to sort them. */
    if ($numMessages = 0) {
        $messagelist = array();
        $msgs = array();
    }
    if (count($messagelist) > 0 ) {
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
                "move_messages.php?msg=$msg&amp;mailbox=$urlMailbox&amp;pos=$pos&amp;where=" . urlencode($search_where) . "&amp;what=".urlencode($search_what),
                $mailbox,
                -1,
                '<div align="left"><b><big>'. _("Folder:") .' '.(($mailbox == 'INBOX') ? _("INBOX") : $mailbox).
                '</big></b></div></td><td align="right">'.
                '<b>' . _("Found") . ' ' . count($messagelist) . ' ' . _("messages") . '</b>'.
                get_selectall_link($start_msg, $sort));
        }
        else {
            mail_message_listing_beginning( $imapConnection,
                "move_messages.php?msg=$msg&amp;mailbox=$urlMailbox&amp;pos=$pos&amp;where=" . urlencode($search_where) . "&amp;what=".urlencode($search_what),
                $mailbox,
                -1,
                '<div align="left"><b><big>'. _("Folder:") .' '.(($mailbox == 'INBOX') ? _("INBOX") : $mailbox).
                '</big></b></div></td><td align="right">'.
                '<b>' . _("Found") . ' ' . count($messagelist) . ' ' . _("messages") . '</b>');
        }
        while ($j < count($msgs)) {
            printMessageInfo($imapConnection, $msgs[$j]["ID"], 0, $j, $mailbox, '', 0, $search_where, $search_what);
            $j++;
        }
        echo '</table></td></tr></table></form>';
        $count_all = count($msgs);
    }
    return $count_all;
}

?>
