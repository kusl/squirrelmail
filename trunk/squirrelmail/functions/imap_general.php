<?php

/**
 * imap_general.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This implements all functions that do general imap functions.
 *
 * $Id$
 */

require_once('../functions/page_header.php');

global $sqimap_session_id;
$sqimap_session_id = 1;

/* Sets an unique session id in order to avoid simultanous sessions crash. */
function sqimap_session_id() {
    global $data_dir, $username, $sqimap_session_id;
    return( sprintf("A%03d", $sqimap_session_id++) );
}

/*
 * Both send a command and accept the result from the command.
 * This is to allow proper session number handling.
 */
function sqimap_run_command_list ($imap_stream, $query, $handle_errors, &$response, &$message) {
    $sid = sqimap_session_id();
    fputs ($imap_stream, $sid . ' ' . $query . "\r\n");
    $read = sqimap_read_data_list ($imap_stream, $sid, $handle_errors, $response, $message, $query );
    return $read;
}

function sqimap_run_command ($imap_stream, $query, $handle_errors, &$response, &$message) {
    $sid = sqimap_session_id();
    fputs ($imap_stream, $sid . ' ' . $query . "\r\n");
    $read = sqimap_read_data ($imap_stream, $sid, $handle_errors, $response, $message, $query);
    return $read;
}


/*
 * Reads the output from the IMAP stream.  If handle_errors is set to true,
 * this will also handle all errors that are received.  If it is not set,
 * the errors will be sent back through $response and $message
 */
function sqimap_read_data_list ($imap_stream, $pre, $handle_errors, &$response, &$message, $query = '') {
    global $color, $squirrelmail_language;

    $read = '';
    $bufsize = 9096;
    $resultlist = array();

    $more_msgs = true;
    while ($more_msgs) {
        $data = array();
        $total_size = 0;

        while (strpos($read, "\n") === false) {
            if(!($read .= fgets($imap_stream, $bufsize))) {
                break;
            }
        }

        /* if (ereg("^\\* [0-9]+ FETCH.*\\{([0-9]+)\\}", $read, $regs)) { */
        if (preg_match('/^\* [0-9]+ FETCH.*\{([0-9]+)\}/', $read, $regs)) {
           $size = $regs[1];
        /* } else if (ereg("^\\* [0-9]+ FETCH", $read, $regs)) { */
        } else if (preg_match('/^\* [0-9]+ FETCH/', $read, $regs)) {
            /* Sizeless response, probably single-line */
            $size = -1;
            $data[] = $read;
            $read = fgets($imap_stream, $bufsize);
        } else {
            $size = -1;
        }
        while (1) {
            while (strpos($read, "\n") === false) {
                if(!($read .= fgets($imap_stream, $bufsize))) {
                    break;
                }
            }
            /* If we know the size, no need to look at the end parameters */
            if ($size > 0) {
                if ($total_size == $size) {
                    /* We've reached the end of this 'message', switch to the next one. */
                    $data[] = $read;
                    break;
                } else if ($total_size > $size) {
                    $difference = $total_size - $size;
                    $total_size = $total_size - strlen($read);
                    $data[] = substr ($read, 0, strlen($read)-$difference);
                    $read = substr ($read, strlen($read)-$difference, strlen($read));
                    break;
                } else {
                    $data[] = $read;
                    $read = fgets($imap_stream, $bufsize);
                    while (strpos($read, "\n") === false) {
                      $read .= fgets($imap_stream, $bufsize);
                    }
                }
                $total_size += strlen($read);
            } else {
                /* if (ereg("^$pre (OK|BAD|NO)(.*)", $read, $regs) || */
                if (preg_match("/^$pre (OK|BAD|NO)(.*)/", $read, $regs) ||
                    /* (($size == -1) && ereg("^\\* [0-9]+ FETCH.*", $read, $regs))) { */
                    (($size == -1) && preg_match('/^\* [0-9]+ FETCH.*/', $read, $regs))) {
                    break;
                } else if ( preg_match('/^\* OK \[PARSE.*/', $read, $regs ) ) {
                    /*
                     * This block has been added in order to avoid the problem
                     * caused by the * OK [PARSE] Missing parameter answer
                     * Please, replace it with a better parsing if you know how.
                     * This block has been updated by
                     * Seth E. Randall <sethr@missoulafcu.org>.  Once we see
                     * one OK [PARSE line, we just go through and keep
                     * tossing them out until we get something different.
                     */
                    while ( preg_match('/^\* OK \[PARSE.*/', $read, $regs ) ) {
                        $read = fgets($imap_stream, $bufsize);
                    }
                    $data[] = $read;
                    $read = fgets ($imap_stream, $bufsize);
                } else if (preg_match('/^\* BYE \[ALERT\](.*)/', $read, $regs)) {
                    /*
                     * It seems that the IMAP server has coughed up a lung
                     * and hung up the connection.  Print any info we have
                     * and abort.
                     */
                    echo _("Please contact your system administrator and report the following error:") . "<br>\n";
                    echo $regs[1];
                    exit;
                } else {
                    $data[] = $read;
                    $read = fgets ($imap_stream, $bufsize);
                }
            }
        }

        /*
         * while (($more_msgs = !ereg("^$pre (OK|BAD|NO)(.*)$", $read, $regs)) &&
         * !ereg("^\\* [0-9]+ FETCH.*", $read, $regs)) {
         */
        while (($more_msgs = !preg_match("/^$pre (OK|BAD|NO)(.*)$/", $read, $regs)) &&
            !preg_match('/^\* [0-9]+ FETCH.*/', $read, $regs)) {
            $read = fgets($imap_stream, $bufsize);
        }
        $resultlist[] = $data;
    }

    $response = $regs[1];
    $message = trim($regs[2]);

    if ($handle_errors == false) {
        return( $resultlist );
    } else if ($response == 'NO') {
        /* ignore this error from M$ exchange, it is not fatal (aka bug) */
        if (strstr($message, 'command resulted in') === false) {
            set_up_language($squirrelmail_language);
            echo "<br><b><font color=$color[2]>\n" .
                 _("ERROR : Could not complete request.") .
                 "</b><br>\n" .
                 _("Query:") .
                 $query . '<br>' .
                 _("Reason Given: ") .
                 $message . "</font><br>\n";
            exit;
        }
    } else if ($response == 'BAD') {
        set_up_language($squirrelmail_language);
        echo "<br><b><font color=$color[2]>\n" .
             _("ERROR : Bad or malformed request.") .
             "</b><br>\n" .
             _("Query:") .
             $query . '<br>' .
             _("Server responded: ") .
             $message . "</font><br>\n";
        exit;
    } else {
        return $resultlist;
    }
}

function sqimap_read_data ($imap_stream, $pre, $handle_errors, &$response, &$message, $query = '') {
    $res = sqimap_read_data_list($imap_stream, $pre, $handle_errors, $response, $message, $query);
    return $res[0];
}

/*
 * Logs the user into the imap server.  If $hide is set, no error messages
 * will be displayed.  This function returns the imap connection handle.
 */
function sqimap_login ($username, $password, $imap_server_address, $imap_port, $hide) {
    global $color, $squirrelmail_language, $HTTP_ACCEPT_LANGUAGE, $onetimepad;

    $imap_stream = fsockopen ( $imap_server_address, $imap_port, $error_number, $error_string, 15);
    if ( !$imap_stream ) {
        return false;
    }
    $server_info = fgets ($imap_stream, 1024);

    /* Decrypt the password */
    $password = OneTimePadDecrypt($password, $onetimepad);

    /* Do some error correction */
    if (!$imap_stream) {
        if (!$hide) {
            set_up_language($squirrelmail_language, true);
            printf (_("Error connecting to IMAP server: %s.")."<br>\r\n", $imap_server_address);
            echo "$error_number : $error_string<br>\r\n";
        }
        exit;
    }

    $query = 'LOGIN "' . quoteIMAP($username) .  '" "' . quoteIMAP($password) . '"';
    $read = sqimap_run_command ($imap_stream, $query, false, $response, $message);

    /* If the connection was not successful, lets see why */
    if ($response != 'OK') {
        if (!$hide) {
            if ($response != 'NO') {
                /* "BAD" and anything else gets reported here. */
                set_up_language($squirrelmail_language, true);
                if ($response == 'BAD') {
                   printf (_("Bad request: %s")."<br>\r\n", $message);
                } else {
                   printf (_("Unknown error: %s") . "<br>\n", $message);
                }
                echo '<br>' . _("Read data:") . "<br>\n";
                if (is_array($read)) {
                    foreach ($read as $line) {
                        echo htmlspecialchars($line) . "<br>\n";
                    }
                }
                exit;
            } else {
                /*
                 * If the user does not log in with the correct
                 * username and password it is not possible to get the
                 * correct locale from the user's preferences.
                 * Therefore, apply the same hack as on the login
                 * screen.
                 *
                 * $squirrelmail_language is set by a cookie when
                 * the user selects language and logs out
                 */
                
                set_up_language($squirrelmail_language, true);
                include_once( '../functions/display_messages.php' );
                logout_error( _("Unknown user or password incorrect.") );                
                session_destroy();
                exit;
            }
        } else {
            exit;
        }
    }
    return $imap_stream;
}

/* Simply logs out the IMAP session */
function sqimap_logout ($imap_stream) {
    /* Logout is not valid until the server returns 'BYE' */
    sqimap_run_command($imap_stream, 'LOGOUT', false, $response, $message);
}

function sqimap_capability($imap_stream, $capability) {
    global $sqimap_capabilities;

    if (!is_array($sqimap_capabilities)) {
        $read = sqimap_run_command($imap_stream, 'CAPABILITY', true, $a, $b);

        $c = explode(' ', $read[0]);
        for ($i=2; $i < count($c); $i++) {
            $cap_list = explode('=', $c[$i]);
            if (isset($cap_list[1])) {
                $sqimap_capabilities[$cap_list[0]] = $cap_list[1];
            } else {
                $sqimap_capabilities[$cap_list[0]] = TRUE;
            }
        }
    }
    if (isset($sqimap_capabilities[$capability])) {
        return $sqimap_capabilities[$capability];
    } else {
        return false;
    }
}

/* Returns the delimeter between mailboxes: INBOX/Test, or INBOX.Test */
function sqimap_get_delimiter ($imap_stream = false) {
    global $sqimap_delimiter, $optional_delimiter;

    /* Use configured delimiter if set */
    if((!empty($optional_delimiter)) && $optional_delimiter != 'detect') {
        return $optional_delimiter;
    }

    /* Do some caching here */
    if (!$sqimap_delimiter) {
        if (sqimap_capability($imap_stream, 'NAMESPACE')) {
            /*
             * According to something that I can't find, this is supposed to work on all systems
             * OS: This won't work in Courier IMAP.
             * OS: According to rfc2342 response from NAMESPACE command is:
             * OS: * NAMESPACE (PERSONAL NAMESPACES) (OTHER_USERS NAMESPACE) (SHARED NAMESPACES)
             * OS: We want to lookup all personal NAMESPACES...
             */
            $read = sqimap_run_command($imap_stream, 'NAMESPACE', true, $a, $b);
            if (eregi('\\* NAMESPACE +(\\( *\\(.+\\) *\\)|NIL) +(\\( *\\(.+\\) *\\)|NIL) +(\\( *\\(.+\\) *\\)|NIL)', $read[0], $data)) {
                if (eregi('^\\( *\\((.*)\\) *\\)', $data[1], $data2)) {
                    $pn = $data2[1];
                }
                $pna = explode(')(', $pn);
                while (list($k, $v) = each($pna)) {
                    $lst = explode('"', $v);
                    if (isset($lst[3])) {
                        $pn[$lst[1]] = $lst[3];
                    } else {
                        $pn[$lst[1]] = '';
                    }
                }
            }
            $sqimap_delimiter = $pn[0];
        } else {
            fputs ($imap_stream, ". LIST \"INBOX\" \"\"\r\n");
            $read = sqimap_read_data($imap_stream, '.', true, $a, $b);
            $quote_position = strpos ($read[0], '"');
            $sqimap_delimiter = substr ($read[0], $quote_position+1, 1);
        }
    }
    return $sqimap_delimiter;
}


/* Gets the number of messages in the current mailbox. */
function sqimap_get_num_messages ($imap_stream, $mailbox) {
    $read_ary = sqimap_run_command ($imap_stream, "EXAMINE \"$mailbox\"", true, $result, $message);
    for ($i = 0; $i < count($read_ary); $i++) {
        if (ereg("[^ ]+ +([^ ]+) +EXISTS", $read_ary[$i], $regs)) {
            return $regs[1];
        }
    }
    return "BUG! Couldn't get number of messages in $mailbox!";
}


/* Returns a displayable email address.
 *     Luke Ehresman <lehresma@css.tayloru.edu>
 *     "Luke Ehresman" <lehresma@css.tayloru.edu>
 *     <lehresma@css.tayloru.edu>
 *     lehresma@css.tayloru.edu (Luke Ehresman)
 *     lehresma@css.tayloru.edu
 * becomes: lehresma@css.tayloru.edu
 */
function sqimap_find_email ($string) {
    if (ereg("<([^>]+)>", $string, $regs)) {
        $string = $regs[1];
    } else if (ereg("([^ ]+@[^ ]+)", $string, $regs)) {
        $string = $regs[1];
    }
    return trim($string);
}


/*
 * Takes the From: field and creates a displayable name.
 *     Luke Ehresman   <lkehresman@yahoo.com>
 *     "Luke Ehresman" <lkehresman@yahoo.com>
 *     lkehresman@yahoo.com (Luke Ehresman)
 * becomes: Luke Ehresman
 *     <lkehresman@yahoo.com>
 * becomes: lkehresman@yahoo.com
 */
function sqimap_find_displayable_name ($string) {
    $string = trim($string);

    if ( ereg('^(.+)<.*>', $string, $regs) ) {
        $orig_string = $string;
        $string = str_replace ('"', '', $regs[1] );
        if (trim($string) == '') {
             $string = sqimap_find_email($orig_string);
        }
        if( $string == '' || $string == ' ' ){
            $string = '&nbsp';
        }
    }
    elseif ( ereg('\((.*)\)', $string, $regs) ) {
        if( ( $regs[1] == '' ) || ( $regs[1] == ' ' ) ){
            if ( ereg('^(.+) \(', $string, $regs) ) {
               $string = ereg_replace( ' \(\)$', '', $string );
            } else {
               $string = '&nbsp';
            }
        } else {
            $string = $regs[1];
        }
    }
    else {
        $string = str_replace ('"', '', sqimap_find_email($string));
    }

    return trim($string);
}

/*
 * Returns the number of unseen messages in this folder
 */
function sqimap_unseen_messages ($imap_stream, $mailbox) {
    $read_ary = sqimap_run_command ($imap_stream, "STATUS \"$mailbox\" (UNSEEN)", true, $result, $message);
    $i = 0;
    while (isset($read_ary[$i])) {
        if (ereg("UNSEEN ([0-9]+)", $read_ary[$i], $regs)) {
            break;
        }
        $i++;
    }
    return $regs[1];
}


/*
 *  Saves a message to a given folder -- used for saving sent messages
 */
function sqimap_append ($imap_stream, $sent_folder, $length) {
    fputs ($imap_stream, sqimap_session_id() . " APPEND \"$sent_folder\" (\\Seen) \{$length}\r\n");
    $tmp = fgets ($imap_stream, 1024);
}

function sqimap_append_done ($imap_stream) {
    fputs ($imap_stream, "\r\n");
    $tmp = fgets ($imap_stream, 1024);
}

?>
