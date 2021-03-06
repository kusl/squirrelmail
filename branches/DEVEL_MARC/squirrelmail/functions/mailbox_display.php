<?php

/**
* mailbox_display.php
*
* Copyright (c) 1999-2004 The SquirrelMail Project Team
* Licensed under the GNU GPL. For full terms see the file COPYING.
*
* This contains functions that display mailbox information, such as the
* table row that has sender, date, subject, etc...
*
* @version $Id$
* @package squirrelmail
*/

/** The standard includes.. */
require_once(SM_PATH . 'functions/strings.php');
require_once(SM_PATH . 'functions/html.php');
require_once(SM_PATH . 'functions/imap_mailbox.php');
require_once(SM_PATH . 'functions/imap_messages.php');
require_once(SM_PATH . 'functions/imap_asearch.php');
require_once(SM_PATH . 'functions/mime.php');
require_once(SM_PATH . 'functions/forms.php');


/**
 * Selects a mailbox for header retrieval.
 * Cache control for message headers is embedded.
 *
 * @param resource $imapConnection imap socket handle
 * @param string   $mailbox mailbox to select and retrieve message headers from
 * @param array    $aConfig array with system config settings and incoming vars
 * @param array    $aProps mailbox specific properties
 * @return array   $aMailbox mailbox array with all relevant information
 * @author Marc Groot Koerkamp
 */
function sqm_api_mailbox_select($imapConnection,$account,$mailbox,$aConfig,$aProps) {

    /**
     * NB: retrieve this from the session before accessing this function
     * and make sure you write it back at the end of the script after
     * the aMailbox var is added so that the headers are added to the cache
     */
    global $mailbox_cache;

    $aDefaultConfigProps = array(
//                'charset'           => 'US-ASCII',
                'user'              => false, /* no pref storage if false */
                'setindex'          => 0,
//                'search'            => 'ALL',
                'max_cache_size'    => SQM_MAX_MBX_IN_CACHE
                );

    $aConfig = array_merge($aDefaultConfigProps,$aConfig);
    $iSetIndx = $aConfig['setindex'];

    $aMbxResponse = sqimap_mailbox_select($imapConnection, $mailbox);

    if ($mailbox_cache) {
        if (isset($mailbox_cache[$account.'_'.$mailbox])) {
            $aCachedMailbox = $mailbox_cache[$account.'_'.$mailbox];
        } else {
            $aCachedMailbox = false;
        }
            /* cleanup cache */
        if (count($mailbox_cache) > $aConfig['max_cache_size'] -1) {
            $aTime = array();
            foreach($mailbox_cache as $cachedmailbox => $aVal) {
                $aTime[$aVal['TIMESTAMP']] = $cachedmailbox;
            }
            if (ksort($aTime,SORT_NUMERIC)) {
                for ($i=0,$iCnt=count($mailbox_cache);$i<($iCnt-$aConfig['max_cache_size']);++$i) {
                    $sOldestMbx = array_shift($aTime);
                    /**
                     * Remove only the UIDSET and MSG_HEADERS from cache because those can
                     * contain large amounts of data.
                     */
                    if (isset($mailbox_cache[$sOldestMbx]['UIDSET'])) {
                        $mailbox_cache[$sOldestMbx]['UIDSET']= false;
                    }
                    if (isset($mailbox_cache[$sOldestMbx]['MSG_HEADERS'])) {
                        $mailbox_cache[$sOldestMbx]['MSG_HEADERS'] = false;
                    }
                }
            }
        }

    } else {
        $aCachedMailbox = false;
    }

    /**
     * Deal with imap servers that do not return the required UIDNEXT or
     * UIDVALIDITY response
     * from a SELECT call (since rfc 3501 it's required).
     */
    if (!isset($aMbxResponse['UIDNEXT']) || !isset($aMbxResponse['UIDVALIDITY'])) {
        $aStatus = sqimap_status_messages($imapConnection,$mailbox,
                                        array('UIDNEXT','UIDVALIDITY'));
        $aMbxResponse['UIDNEXT'] = $aStatus['UIDNEXT'];
        $aMbxResponse['UIDVALIDTY'] = $aStatus['UIDVALIDITY'];
    }

    $aMailbox['ACCOUNT'] = $account;
    $aMailbox['UIDSET'][$iSetIndx] = false;
    $aMailbox['ID'] = false;
    $aMailbox['SETINDEX'] = $iSetIndx;

    if ($aCachedMailbox) {
        /**
         * Validate integrity of cached data
         */
        if ($aCachedMailbox['EXISTS'] == $aMbxResponse['EXISTS'] &&
            $aMbxResponse['EXISTS'] &&
            $aCachedMailbox['UIDVALIDITY'] == $aMbxResponse['UIDVALIDITY'] &&
            $aCachedMailbox['UIDNEXT']  == $aMbxResponse['UIDNEXT'] &&
            isset($aCachedMailbox['SEARCH'][$iSetIndx]) &&
            (!isset($aConfig['search']) || /* always set search from the searchpage */
             $aCachedMailbox['SEARCH'][$iSetIndx] == $aConfig['search'])) {
            if (isset($aCachedMailbox['MSG_HEADERS'])) {
                $aMailbox['MSG_HEADERS'] = $aCachedMailbox['MSG_HEADERS'];
            }
            $aMailbox['ID'] =  $aCachedMailbox['ID'];
            if (isset($aCachedMailbox['UIDSET'][$iSetIndx]) && $aCachedMailbox['UIDSET'][$iSetIndx]) {
                if (isset($aProps[MBX_PREF_SORT]) &&  $aProps[MBX_PREF_SORT] != $aCachedMailbox['SORT'] ) {
                    $newsort = $aProps[MBX_PREF_SORT];
                    $oldsort = $aCachedMailbox['SORT'];
                    /**
                     * If it concerns a reverse sort we do not need to invalidate
                     * the cached sorted UIDSET, a reverse is sufficient.
                     */
                    if ((($newsort % 2) && ($newsort + 1 == $oldsort)) ||
                        (!($newsort % 2) && ($newsort - 1 == $oldsort))) {
                        $aMailbox['UIDSET'][$iSetIndx] = array_reverse($aCachedMailbox['UIDSET'][$iSetIndx]);
                    } else {
                        $server_sort_array = false;
                        $aMailbox['MSG_HEADERS'] = false;
                        $aMailbox['ID'] = false;
                    }
                    // store the new sort value in the mailbox pref
                    if ($aConfig['user']) {
                        // FIXME, in ideal situation, we write back the
                        // prefs at the end of the script
                        setUserPref($aConfig['user'],'pref_'.$account.'_'.$mailbox,serialize($aProps));
                    }
                } else {
                    $aMailbox['UIDSET'][$iSetIndx] = $aCachedMailbox['UIDSET'][$iSetIndx];
                }
            }
        }
    }
    /**
     * Restore the offset in the paginator if no new offset is provided.
     */
    if (isset($aMailbox['UIDSET'][$iSetIndx]) && !isset($aConfig['offset']) && $aCachedMailbox['OFFSET']) {
        $aMailbox['OFFSET'] =  $aCachedMailbox['OFFSET'];
        $aMailbox['PAGEOFFSET'] =  $aCachedMailbox['PAGEOFFSET'];
    } else {
        $aMailbox['OFFSET'] = (isset($aConfig['offset']) && $aConfig['offset']) ? $aConfig['offset'] -1 : 0;
        $aMailbox['PAGEOFFSET'] = (isset($aConfig['offset']) && $aConfig['offset']) ? $aConfig['offset'] : 1;
    }
    /**
     * Restore the number of messages in the result set
     */
    if (isset($aCachedMailbox['TOTAL'][$iSetIndx]) && $aCachedMailbox['TOTAL'][$iSetIndx]) {
        $aMailbox['TOTAL'][$iSetIndx] =  $aCachedMailbox['TOTAL'][$iSetIndx];
    }

    /**
     * Restore the showall value no new showall value is provided.
     */
    if (isset($aMailbox['UIDSET'][$iSetIndx]) && !isset($aConfig['showall']) &&
        isset($aCachedMailbox['SHOWALL'][$iSetIndx]) && $aCachedMailbox['SHOWALL'][$iSetIndx]) {
        $aMailbox['SHOWALL'][$iSetIndx] =  $aCachedMailbox['SHOWALL'][$iSetIndx];
    } else {
        $aMailbox['SHOWALL'][$iSetIndx] = (isset($aConfig['showall']) && $aConfig['showall']) ? 1 : 0;
    }

    /**
     * Restore the sort order if no new sort order is provided.
     */
    if (!isset($aProps[MBX_PREF_SORT]) && isset($aCachedMailbox['SORT'])) {
        $aMailbox['SORT'] = $aCachedMailbox['SORT'];
    } else {
        $aMailbox['SORT'] =  (isset($aProps[MBX_PREF_SORT])) ? $aProps[MBX_PREF_SORT] : 0;
    }

    /**
     * Restore the number of message to show per page when no new limit is provided
     */
    if (!isset($aProps[MBX_PREF_LIMIT]) && isset($aCachedMailbox['LIMIT'])) {
        $aMailbox['LIMIT'] = $aCachedMailbox['LIMIT'];
    } else {
        $aMailbox['LIMIT'] =  (isset($aProps[MBX_PREF_LIMIT])) ? $aProps[MBX_PREF_LIMIT] : 15;
    }

    /**
     * Restore the ordered columns to show when no new ordered columns are provided
     */
    if (!isset($aProps[MBX_PREF_COLUMNS]) && isset($aCachedMailbox['COLUMNS'])) {
        $aMailbox['COLUMNS'] = $aCachedMailbox['COLUMNS'];
    } else {
        $aMailbox['COLUMNS'] =  (isset($aProps[MBX_PREF_COLUMNS])) ? $aProps[MBX_PREF_COLUMNS] :
            array(SQM_COL_FLAGS,SQM_COL_FROM, SQM_COL_SUBJ, SQM_COL_FLAGS);
    }

    if (!isset($aProps[MBX_PREF_AUTO_EXPUNGE]) && isset($aCachedMailbox['AUTO_EXPUNGE'])) {
        $aMailbox['AUTO_EXPUNGE'] = $aCachedMailbox['AUTO_EXPUNGE'];
    } else {
        $aMailbox['AUTO_EXPUNGE'] =  (isset($aProps[MBX_PREF_AUTO_EXPUNGE])) ? $aProps[MBX_PREF_AUTO_EXPUNGE] : false;
    }
    if (!isset($aConfig['search']) && isset($aCachedMailbox['SEARCH'][$iSetIndx])) {
        $aMailbox['SEARCH'][$iSetIndx] = $aCachedMailbox['SEARCH'][$iSetIndx];
    } else {
        $aMailbox['SEARCH'][$iSetIndx] =  (isset($aConfig['search'])) ? $aConfig['search'] : 'ALL';
    }
    if (!isset($aConfig['charset']) && isset($aCachedMailbox['CHARSET'][$iSetIndx])) {
        $aMailbox['CHARSET'][$iSetIndx] = $aCachedMailbox['CHARSET'][$iSetIndx];
    } else {
        $aMailbox['CHARSET'][$iSetIndx] =  (isset($aConfig['charset'])) ? $aConfig['charset'] : 'US-ASCII';
    }

    $aMailbox['NAME'] = $mailbox;
    $aMailbox['EXISTS'] = $aMbxResponse['EXISTS'];
    $aMailbox['SEEN'] = (isset($aMbxResponse['SEEN'])) ? $aMbxResponse['SEEN'] : $aMbxResponse['EXISTS'];
    $aMailbox['RECENT'] = (isset($aMbxResponse['RECENT'])) ? $aMbxResponse['RECENT'] : 0;
    $aMailbox['UIDVALIDITY'] = $aMbxResponse['UIDVALIDITY'];
    $aMailbox['UIDNEXT'] = $aMbxResponse['UIDNEXT'];
    $aMailbox['PERMANENTFLAGS'] = $aMbxResponse['PERMANENTFLAGS'];
    $aMailbox['RIGHTS'] = $aMbxResponse['RIGHTS'];

    /* decide if we are thread sorting or not */
    if ($aMailbox['SORT'] & SQSORT_THREAD) {
        if (!sqimap_capability($imapConnection,'THREAD')) {
            $aMailbox['SORT'] ^= SQSORT_THREAD;
        } else {
            $aMailbox['THREAD_INDENT'] = $aCachedMailbox['THREAD_INDENT'];
        }
    } else {
        $aMailbox['THREAD_INDENT'] = false;
    }

    /* set a timestamp for cachecontrol */
    $aMailbox['TIMESTAMP'] = time();
    return $aMailbox;
}

/**
 * Fetch the message headers for a mailbox. Settings are part of the aMailbox
 * array.
 *
 * @param resource $imapConnection imap socket handle
 * @param array    $aMailbox (reference) mailbox retrieved from sqm_api_mailbox_select
 * @return error   $error error number
 * @author Marc Groot Koerkamp
 */
function fetchMessageHeaders($imapConnection, &$aMailbox, $aFetchHeaders) {

    /* FIX ME, this function is kind of big, maybe we can split it up in
       a couple of functions. Make sure the functions are private and starts with _
       Also make sure that the error codes are propagated */


    /**
     * Retrieve the UIDSET.
     * Setindex is used to be able to store multiple uid sets. That will make it
     * possible to display the mailbox multiple times in different sort order
     * or to store serach results separate from normal mailbox view.
     */
    $iSetIndx =  (isset($aMailbox['SETINDEX'])) ? $aMailbox['SETINDEX'] : 0;

    $iLimit = ($aMailbox['SHOWALL'][$iSetIndx]) ? $aMailbox['EXISTS'] : $aMailbox['LIMIT'];
    /**
     * Adjust the start_msg
     */
    $start_msg = $aMailbox['PAGEOFFSET'];
    if($aMailbox['PAGEOFFSET'] > $aMailbox['EXISTS']) {
        $start_msg -= $aMailbox['LIMIT'];
        if($start_msg < 1) {
            $start_msg = 1;
        }
    }


    if (is_array($aMailbox['UIDSET'])) {
        $aUid =& $aMailbox['UIDSET'][$iSetIndx];
    } else {
        $aUid = false;
    }

    $error = 0;
    $aFetchItems = $aHeaderItems = array();
    // initialize the fields we want to retrieve:
    foreach ($aFetchHeaders as $v) {
      switch ($v) {
        case SQM_COL_DATE:       $aHeaderFields[] = 'Date';         break;
        case SQM_COL_TO:         $aHeaderFields[] = 'To';           break;
        case SQM_COL_CC:         $aHeaderFields[] = 'Cc';           break;
        case SQM_COL_FROM:       $aHeaderFields[] = 'From';         break;
        case SQM_COL_SUBJ:       $aHeaderFields[] = 'Subject';      break;
        case SQM_COL_PRIO:       $aHeaderFields[] = 'X-Priority';   break;
        case SQM_COL_ATTACHMENT: $aHeaderFields[] = 'Content-Type'; break;
        case SQM_COL_INT_DATE:   $aFetchItems[]   = 'INTERNALDATE'; break;
        case SQM_COL_FLAGS:      $aFetchItems[]   = 'FLAGS';        break;
        case SQM_COL_SIZE:       $aFetchItems[]   = 'RFC822.SIZE';  break;
        default: break;
      }
    }

    /**
     * A uidset with sorted uid's is available. We can use the cache
     */
    if (($aMailbox['SORT'] != SQSORT_NONE || $aMailbox['SEARCH'][$iSetIndx] != 'ALL') &&
         isset($aUid) && $aUid ) {

        // limit the cache to SQM_MAX_PAGES_IN_CACHE
        if (!$aMailbox['SHOWALL'][$iSetIndx]) {
            $iMaxMsgs = $iLimit * SQM_MAX_PAGES_IN_CACHE;
            $iCacheSize = count($aMailbox['MSG_HEADERS']);
            if ($iCacheSize > $iMaxMsgs) {
                $iReduce = $iCacheSize - $iMaxMsgs;
                foreach ($aMailbox['MSG_HEADERS'] as $iUid => $value) {
                    if ($iReduce) {
                        unset($aMailbox['MSG_HEADERS'][$iUid]);
                    } else {
                        break;
                    }
                    --$iReduce;
                }
            }
        }

        $id_slice = array_slice($aUid,$start_msg-1,$iLimit);
        /* do some funky cache checks */
        $aUidCached = array_keys($aMailbox['MSG_HEADERS']);
        $aUidNotCached = array_values(array_diff($id_slice,$aUidCached));
        /**
         * $aUidNotCached contains an array with UID's which need to be fetched to
         * complete the needed message headers.
         */
        if (count($aUidNotCached)) {
            $aMsgs = sqimap_get_small_header_list($imapConnection,$aUidNotCached,
                                                    $aHeaderFields,$aFetchItems);
            // append the msgs to the existend headers
            $aMailbox['MSG_HEADERS'] += $aMsgs;
        }
    } else {
        /**
         * Initialize the sorted UID list or initiate a UID list with search
         * results and fetch the visible message headers
         */
        if ($aMailbox['SORT'] != SQSORT_NONE || $aMailbox['SEARCH'][$iSetIndx] != 'ALL') {
            if ($aMailbox['SEARCH'][$iSetIndx] && $aMailbox['SORT'] == 0) {
                $aUid = sqimap_run_search($imapConnection, $aMailbox['SEARCH'][$iSetIndx], $aMailbox['CHARSET'][$iSetIndx]);
            } else {
                $error = _get_sorted_msgs_list($imapConnection,$aMailbox,$error);
                $aUid = $aMailbox['UIDSET'][$iSetIndx];
            }
            if (!$error) {
                /**
                 * Number of messages is the resultset
                 */
                $aMailbox['TOTAL'][$iSetIndx] = count($aUid);
                $id_slice = array_slice($aUid,$aMailbox['OFFSET'], $iLimit);
                if (count($id_slice)) {
                    $aMailbox['MSG_HEADERS'] = sqimap_get_small_header_list($imapConnection,$id_slice,
                        $aHeaderFields,$aFetchItems);
                } else {
                    $error = 1; // FIX ME, define an error code
                }
            }
        } else {
            // limit the cache to SQM_MAX_PAGES_IN_CACHE
            if (!$aMailbox['SHOWALL'][$iSetIndx] && isset($aMailbox['MSG_HEADERS']) && is_array($aMailbox['MSG_HEADERS'])) {
                $iMaxMsgs = $iLimit * SQM_MAX_PAGES_IN_CACHE;
                $iCacheSize = count($aMailbox['MSG_HEADERS']);
                if ($iCacheSize > $iMaxMsgs) {
                    $iReduce = $iCacheSize - $iMaxMsgs;
                    foreach ($aMailbox['MSG_HEADERS'] as $iUid => $value) {
                        if ($iReduce) {
                            $iId = $aMailbox['MSG_HEADERS'][$iUid]['ID'];
                            unset($aMailbox['MSG_HEADERS'][$iUid]);
                            unset($aMailbox['ID'][$iId]);
                        } else {
                            break;
                        }
                        --$iReduce;
                    }
                }
            }

            /**
            * retrieve messages by sequence id's and fetch the UID to retrieve
            * the UID. for sorted lists this is not needed because a UID FETCH
            * automaticly add the UID value in fetch results
            **/
            $aFetchItems[] = 'UID';

            //create id range
            $iRangeStart = $aMailbox['EXISTS'] - $aMailbox['OFFSET'];
            $iRangeEnd   = ($iRangeStart > $iLimit) ?
                            ($iRangeStart - $iLimit+1):1;

            $id_slice = range($iRangeStart, $iRangeEnd);
            /**
             * Non sorted mailbox with cached message headers
             */
            if (isset($aMailbox['ID']) && is_array($aMailbox['ID'])) {
                // the fetched id => uid relation
                $aId = $aMailbox['ID'];
                $aIdCached = array();
                foreach ($aId as $iId => $iUid) {
                    if (isset($aMailbox['MSG_HEADERS'][$iUid])) {
                        if ($iId <= $iRangeStart && $iId >= $iRangeEnd) {
                            $aIdCached[] = $iId;
                        }
                    }
                }
                $aIdNotCached = array_diff($id_slice,$aIdCached);
            } else {
                $aIdNotCached = $id_slice;
            }

            if (count($aIdNotCached)) {
                $aMsgs = sqimap_get_small_header_list($imapConnection,$aIdNotCached,
                    $aHeaderFields,$aFetchItems);
                // append the msgs to the existend headers
                if (isset($aMailbox['MSG_HEADERS']) && is_array($aMailbox['MSG_HEADERS'])) {
                    $aMailbox['MSG_HEADERS'] += $aMsgs;
                } else {
                    $aMailbox['MSG_HEADERS'] = $aMsgs;
                }
                // update the ID array
                foreach ($aMsgs as $iUid => $aMsg) {
                    if (isset($aMsg['ID'])) {
                        $aMailbox['ID'][$aMsg['ID']] = $iUid;
                    }
                }
            }

            /**
             * In unsorted state we show newest messages first which means
             * that the UIDSET which represents the order of the messages
             * should contain a high to low ordered UID list
             */
            $aSortedUidList = array();
            foreach ($id_slice as $iId) {
                if (isset($aMailbox['ID'][$iId])) {
                    $aSortedUidList[] = $aMailbox['ID'][$iId];
                }
            }
            $aMailbox['UIDSET'][$iSetIndx] = $aSortedUidList;
            $aMailbox['OFFSET'] = 0;
            /**
             * Number of messages is the resultset
             */
            $aMailbox['TOTAL'][$iSetIndx] = $aMailbox['EXISTS'];
        }
    }
    return $error;
}

function prepareMessageList(&$aMailbox, $aProps) {
    /* retrieve the properties */
    $my_email_address = (isset($aProps['email'])) ? $aProps['email'] : false;
    $highlight_list   = (isset($aProps['config']['highlight_list'])) ? $aProps['config']['highlight_list'] : false;
    $aColumnDesc      = (isset($aProps['columns'])) ? $aProps['columns'] : false;
    $aExtraColumns    = (isset($aProps['extra_columns'])) ? $aProps['extra_columns'] : array();
    $iAccount         = (isset($aProps['account'])) ? (int) $aProps['account'] : 0;
    $sMailbox         = (isset($aProps['mailbox'])) ? $aProps['mailbox'] : false;
    $sTargetModule    = (isset($aProps['module'])) ? $aProps['module'] : 'read_body';

    /**
     * Only retrieve values for displayable columns
     */
    foreach ($aColumnDesc as $k => $v) {
        switch ($k) {
          case SQM_COL_FROM:       $aCol[SQM_COL_FROM]       = 'from';         break;
          case SQM_COL_DATE:       $aCol[SQM_COL_DATE]       = 'date';         break;
          case SQM_COL_SUBJ:       $aCol[SQM_COL_SUBJ]       = 'subject';      break;
          case SQM_COL_FLAGS:      $aCol[SQM_COL_FLAGS]      = 'FLAGS';        break;
          case SQM_COL_SIZE:       $aCol[SQM_COL_SIZE]       = 'SIZE';         break;
          case SQM_COL_PRIO:       $aCol[SQM_COL_PRIO]       = 'x-priority';   break;
          case SQM_COL_ATTACHMENT: $aCol[SQM_COL_ATTACHMENT] = 'content-type'; break;
          case SQM_COL_INT_DATE:   $aCol[SQM_COL_INT_DATE]   = 'INTERNALDATE'; break;
          case SQM_COL_TO:         $aCol[SQM_COL_TO]         = 'to';           break;
          case SQM_COL_CC:         $aCol[SQM_COL_CC]         = 'cc';           break;
          case SQM_COL_BCC:        $aCol[SQM_COL_BCC]        = 'bcc';          break;
          default: break;
        }
    }
    $aFormattedMessages = array();


    $iSetIndx    =  $aMailbox['SETINDEX'];
    $aId         =  $aMailbox['UIDSET'][$iSetIndx];
    $aHeaders    =& $aMailbox['MSG_HEADERS']; /* use a reference to avoid a copy.
                                                 MSG_HEADERS can contain large amounts of data */
    $iOffset     =  $aMailbox['OFFSET'];
    $sort        =  $aMailbox['SORT'];
    $iPageOffset =  $aMailbox['PAGEOFFSET'];
    $sMailbox    =  $aMailbox['NAME'];
    $sSearch     =  (isset($aMailbox['SEARCH'][$aMailbox['SETINDEX']]) &&
                    $aMailbox['SEARCH'][$aMailbox['SETINDEX']] != 'ALL') ? $aMailbox['SEARCH'][$aMailbox['SETINDEX']] : false;
    $aSearch     =  ($sSearch) ? array('search.php',$aMailbox['SETINDEX']) : null;
    /* avoid improper usage */
    if ($sMailbox && isset($iAccount) && $sTargetModule) {
        $aInitQuery  = array("account=$iAccount",'mailbox='.urlencode($sMailbox));
    } else {
        $aInitQuery = false;
    }

    if ($aMailbox['SORT'] & SQSORT_THREAD) {
        $aIndentArray =& $aMailbox['THREAD_INDENT'][$aMailbox['SETINDEX']];
        $bThread = true;
    } else {
        $bThread = false;
    }
    /*
     * Retrieve value for checkbox column
     */
    if (!sqgetGlobalVar('checkall',$checkall,SQ_GET)) {
        $checkall = false;
    }

    /*
     * Loop through and display the info for each message.
     */
    $iEnd = ($aMailbox['SHOWALL'][$iSetIndx]) ? $aMailbox['EXISTS'] : $iOffset + $aMailbox['LIMIT'];
    for ($i=$iOffset,$t=0;$i<$iEnd;++$i) {
        if (isset($aId[$i])) {

            $bHighLight = false;
            $value = $title = $link = $target = '';
            $aQuery = ($aInitQuery) ? $aInitQuery : false;
            $aMsg = $aHeaders[$aId[$i]];
            if (isset($aSearch) && count($aSearch) > 1 && $aQuery) {
                $aQuery[] = "where=". $aSearch[0];
                $aQuery[] = "what=" . $aSearch[1];
            }
            $iUid      = (isset($aMsg['UID'])) ? $aMsg['UID'] : $aId[$i];
            if ($aQuery) {
                $aQuery[] = "passed_id=$aId[$i]";
                $aQuery[] = "startMessage=$iPageOffset";
            }

            foreach ($aCol as $k => $v) {
                $link = $target = $title = '';
                $aColumns[$k] = array();
                $value = (isset($aMsg[$v]))  ? $aMsg[$v]  : '';
                $sUnknown = _("Unknown recipient");
                switch ($k) {
                case SQM_COL_FROM:
                    $sUnknown = _("Unknown sender");
                case SQM_COL_TO:
                case SQM_COL_CC:
                case SQM_COL_BCC:
                    $sTmp = false;
                    if ($value) {
                        if ($highlight_list && !$bHighLight) {
                            $bHighLight = highlightMessage($aCol[$k], $value, $highlight_list,$aFormattedMessages[$iUid]);
                        }
                        $sTmp = getAddressString(parseRFC822Address($value),array('best' => true));
                        if (isset($aColumnDesc[$k]['truncate']) && $aColumnDesc[$k]['truncate']) {
                            $sTrunc = truncateWithEntities($sTmp, $aColumnDesc[$k]['truncate']);
                            $title = ($sTrunc != $sTmp) ? htmlspecialchars($sTmp) : '';
                            $sTmp = $sTrunc;
                        }
                    }
                    $value = ($sTmp) ? htmlspecialchars($sTmp) : $sUnknown;
                    break;
                case SQM_COL_SUBJ:
                    if ($highlight_list && !$bHighLight) {
                        $bHighLight = highlightMessage('SUBJECT', $value, $highlight_list, $aFormattedMessages[$iUid]);
                    }
                    $iIndent = (isset($aIndentArray[$aId[$i]])) ? $aIndentArray[$aId[$i]] : 0;
                    if (isset($aColumnDesc[$k]['truncate']) && $aColumnDesc[$k]['truncate']) {
                        $sTmp = truncateWithEntities($value, $aColumnDesc[$k]['truncate']-$iIndent);
                        $title = ($sTmp != $value) ? htmlspecialchars($value) : '';
                        $value = $sTmp;
                    }
                    /* generate the link to the message */
                    if ($aQuery) {
                        // TODO, $sTargetModule should be a query parameter so that we can use a single entrypoint
                        $link = $sTargetModule.'.php?' . implode('&amp;',$aQuery);
                    }

                    $value = (trim($value)) ? $value : _("(no subject)");
                    /* add thread indentation */
                    $aColumns[$k]['indent']  = $iIndent;
                    $value = htmlspecialchars($value);
                    break;
                case SQM_COL_SIZE:
                    $value = show_readable_size($value);
                    break;
                case SQM_COL_DATE:
                case SQM_COL_INT_DATE:
                    $value = getDateString(getTimeStamp(explode(' ',trim($value))));
                    break;
                case SQM_COL_FLAGS:
                    $aFlagColumn = array('seen' => false,
                                         'deleted'=>false,
                                         'answered'=>false,
                                         'flagged' => false,
                                         'draft' => false);
                    foreach ($value as $sFlag => $value) {
                        switch ($sFlag) {
                          case '\\seen'    : $aFlagColumn['seen']     = true; break;
                          case '\\deleted' : $aFlagColumn['deleted']  = true; break;
                          case '\\answered': $aFlagColumn['answered'] = true; break;
                          case '\\flagged' : $aFlagColumn['flagged']  = true; break;
                          case '\\draft'   : $aFlagColumn['draft']    = true; break;
                          default:  break;
                        }
                    }
                    $value = $aFlagColumn;
                    break;
                case SQM_COL_PRIO:
                    $value = ($value) ? (int) $value : 3;
                    break;
                case SQM_COL_ATTACHMENT:
                    $value = (is_array($value) && $value[0] == 'multipart' && $value[1] == 'mixed') ? true : false;
                    break;
                case SQM_COL_CHECK:
                    $value = $checkall;
                    break;
                default : break;
                }
                if ($title)  { $aColumns[$k]['title']  = $title;  }
                if ($link)   { $aColumns[$k]['link']   = $link;   }
                if ($target) { $aColumns[$k]['target'] = $target; }
                $aColumns[$k]['value']  = $value;
            }
            /* columns which will not be displayed but should be inspected
               because the highlight list contains rules with those columns */
            foreach ($aExtraColumns as $k) {
                if ($highlight_list && !$bHighLight) {
                     $bHighLight = highlightMessage($k, $value, $highlight_list,$aFormattedMessages[$iUid]);
                }
            }
            $aFormattedMessages[$iUid]['columns'] = $aColumns;

        } else {
            break;
        }
    }
    return $aFormattedMessages;
}



function highlightMessage($sCol, $sVal, $highlight_list, &$aFormat) {


    if (!is_array($highlight_list) && count($highlight_list) == 0) {
        return false;
    }
    $hlt_color = false;
    $sCol = strtoupper($sCol);
    //echo "$sCol $sVal<br>";
    foreach ($highlight_list as $highlight_list_part) {
        if (trim($highlight_list_part['value'])) {
            $high_val   = strtolower($highlight_list_part['value']);
            $match_type = strtoupper($highlight_list_part['match_type']);
            if($match_type == 'TO_CC') {
                if ($sCol == 'TO' || $sCol == 'CC') {
                    $match_type = $sCol;
                } else {
                    continue;
                }
            } else {
                if ($match_type != $sCol) {
                    continue;
                }
            }
            if (strpos(strtolower($sVal),$high_val) !== false) {
                 $hlt_color = $highlight_list_part['color'];
                 break;
            }
        }
    }
    if ($hlt_color) {
        $aFormat['row']['color'] = $hlt_color;
        return true;
    } else {
        return false;
    }
}

function setUserPref($username, $pref, $value) {
    global $data_dir;
    setPref($data_dir,$username,$pref,$value);
}

/**
* Execute the sorting for a mailbox
*
* @param  resource $imapConnection Imap connection
* @param  array    $aMailbox (reference) Mailbox retrieved with sqm_api_mailbox_select
* @return int      $error (reference) Error number
* @private
* @author Marc Groot Koerkamp
*/
function _get_sorted_msgs_list($imapConnection,&$aMailbox) {
    $iSetIndx = (isset($aMailbox['SETINDEX'])) ? $aMailbox['SETINDEX'] : 0;
    $bDirection = ($aMailbox['SORT'] % 2);
    $error = 0;
    if (!$aMailbox['SEARCH'][$iSetIndx]) {
        $aMailbox['SEARCH'][$iSetIndx] = 'ALL';
    }
    if (($aMailbox['SORT'] & SQSORT_THREAD) && sqimap_capability($imapConnection,'THREAD')) {
        $aRes = get_thread_sort($imapConnection,$aMailbox['SEARCH'][$iSetIndx]);
        if ($aRes === false) {
            $aMailbox['SORT'] -= SQSORT_THREAD;
            $error = 1; // fix me, define an error code;
        } else {
            $aMailbox['UIDSET'][$iSetIndx] = $aRes[0];
            $aMailbox['THREAD_INDENT'][$iSetIndx] = $aRes[1];
        }
    } else {
        if (sqimap_capability($imapConnection,'SORT')) {
             $sSortField = _getSortField($aMailbox['SORT'],true);
             $id = sqimap_get_sort_order($imapConnection, $sSortField, $bDirection, $aMailbox['SEARCH'][$iSetIndx]);
             if ($id === false) {
                 $error = 1; // fix me, define an error code
             } else {
                $aMailbox['UIDSET'][$iSetIndx] = $id;
             }
        } else {
             $id = NULL;
             if ($aMailbox['SEARCH'][$iSetIndx] != 'ALL') {
                $id = sqimap_run_search($imapConnection, $aMailbox['SEARCH'][$iSetIndx], $aMailbox['CHARSET'][$iSetIndx]);
             }
             $sSortField = _getSortField($aMailbox['SORT'],false);
             $aMailbox['UIDSET'][$iSetIndx] = get_squirrel_sort($imapConnection, $sSortField, $bDirection, $id);
        }
    }
    return $error;
}

/**
* Does the $srt $_GET var to field mapping
*
* @param int $srt Field to sort on
* @param bool $bServerSort Server sorting is true
* @return string $sSortField Field to sort on
* @private
*/
function _getSortField($sort,$bServerSort) {
    switch($sort) {
        case SQSORT_NONE:
            $sSortField = 'UID';
            break;
        case SQSORT_DATE_ASC:
        case SQSORT_DATE_DESC:
            $sSortField = 'DATE';
            break;
        case SQSORT_FROM_ASC:
        case SQSORT_FROM_DESC:
            $sSortField = 'FROM';
            break;
        case SQSORT_SUBJ_ASC:
        case SQSORT_SUBJ_DESC:
            $sSortField = 'SUBJECT';
            break;
        case SQSORT_SIZE_ASC:
        case SQSORT_SIZE_DESC:
            $sSortField = ($bServerSort) ? 'SIZE' : 'RFC822.SIZE';
            break;
        case SQSORT_TO_ASC:
        case SQSORT_TO_DESC:
            $sSortField = 'TO';
            break;
        case SQSORT_CC_ASC:
        case SQSORT_CC_DESC:
            $sSortField = 'CC';
            break;
        case SQSORT_INT_DATE_ASC:
        case SQSORT_INT_DATE_DESC:
            $sSortField = ($bServerSort) ? 'ARRIVAL' : 'INTERNALDATE';
            break;
        case SQSORT_THREAD:
            break;
        default: $sSortField = 'UID';
            break;

    }
    return $sSortField;
}




/**
* This function loops through a group of messages in the mailbox
* and shows them to the user.
*
* @param resource $imapConnection
* @param array    $aMailbox associative array with mailbox related vars
* @param array    $aProps
*/
function showMessagesForMailbox($imapConnection, &$aMailbox,$aProps) {
    global $color, $PHP_SELF;
    global $boxes;

    $highlight_list    = (isset($aProps['config']['highlight_list'])) ? $aProps['config']['highlight_list'] : false;
    $aColumnsDesc      = (isset($aProps['columns'])) ? $aProps['columns'] : false;
    $iAccount          = (isset($aProps['account'])) ? (int) $aProps['account'] : 0;
    $sMailbox          = (isset($aProps['mailbox'])) ? $aProps['mailbox'] : false;
    $sTargetModule     = (isset($aProps['module'])) ? $aProps['module'] : 'read_body';
    $show_flag_buttons = (isset($aProps['config']['show_flag_buttons'])) ? $aProps['config']['show_flag_buttons'] : true;
    $lastTargetMailbox = (isset($aProps['config']['lastTargetMailbox'])) ? $aProps['config']['lastTargetMailbox'] : '';
    $aOrder = array_keys($aProps['columns']);
    $trash_folder      = (isset($aProps['config']['trash_folder']) && $aProps['config']['trash_folder'])
                          ? $aProps['config']['trash_folder'] : false;
    $sent_folder       = (isset($aProps['config']['sent_folder']) && $aProps['config']['sent_folder'])
                          ? $aProps['config']['sent_folder'] : false;
    $draft_folder      = (isset($aProps['config']['draft_folder']) && $aProps['config']['draft_folder'])
                          ? $aProps['config']['draft_folder'] : false;
    $page_selector     = (isset($aProps['config']['page_selector'])) ? $aProps['config']['page_selector'] : false;
    $page_selector_max = (isset($aProps['config']['page_selector_max'])) ? $aProps['config']['page_selector_max'] : 10;


    /*
     * Form ID
     */
    static $iFormId;

    if (!isset($iFormId)) {
        $iFormId=1;
    } else {
        ++$iFormId;
    }

    $aFetchColumns = $aColumnsDesc;
    if (isset($aFetchColumns[SQM_COL_CHECK])) {
        unset($aFetchColumns[SQM_COL_CHECK]);
    }

    /* before we fetch the message headers, check if we need to fetch extra columns
       to make the message highlighning work
     */
    if (is_array($highlight_list) && count($highlight_list)) {
        $aHighlightColumns = array();
        foreach ($highlight_list as $highlight_list_part) {
            if (trim($highlight_list_part['value'])) {
                $match_type = strtoupper($highlight_list_part['match_type']);
                $aHighlightColumns = array();
                switch ($match_type) {
                    case 'TO_CC':
                        $aHighlightColumns[SQM_COL_TO] = true;
                        $aHighlightColumns[SQM_COL_CC] = true;
                        break;
                    case 'TO': $aHighlightColumns[SQM_COL_TO] = true; break;
                    case 'CC': $aHighlightColumns[SQM_COL_CC] = true; break;
                    case 'FROM': $aHighlightColumns[SQM_COL_FROM] = true; break;
                    case 'SUBJECT':$aHighlightColumns[SQM_COL_SUBJ] = true; break;
                }
            }
        }
        $aExtraColumns = array();
        foreach ($aHighlightColumns as $k => $v) {
            if (!isset($aFetchColumns[$k])) {
                $aExtraColumns[]  = $k;
                $aFetchColumns[$k] = true;
            }
        }
        if (count($aExtraColumns)) {
            $aProps['extra_columns'] = $aExtraColumns;
        }
    }
    $aFetchColumns = array_keys($aFetchColumns);


    if (fetchMessageHeaders($imapConnection, $aMailbox, $aFetchColumns)) {
        return false;
    } else {
        $aMessages = prepareMessageList($aMailbox, $aProps);
    }

    $iSetIndx = $aMailbox['SETINDEX'];
    $iLimit = ($aMailbox['SHOWALL'][$iSetIndx]) ? $aMailbox['EXISTS'] : $aMailbox['LIMIT'];
    $iEnd = ($aMailbox['PAGEOFFSET'] + ($iLimit - 1) < $aMailbox['EXISTS']) ?
             $aMailbox['PAGEOFFSET'] + $iLimit - 1 : $aMailbox['EXISTS'];

    $iNumberOfMessages = $aMailbox['TOTAL'][$iSetIndx];

    $php_self = $PHP_SELF;

    $urlMailbox = urlencode($aMailbox['NAME']);

    if (preg_match('/^(.+)\?.+$/',$php_self,$regs)) {
        $source_url = $regs[1];
    } else {
        $source_url = $php_self;
    }

    $baseurl = $source_url.'?mailbox=' . urlencode($aMailbox['NAME']) .'&amp;account='.$aMailbox['ACCOUNT'];
    $where = urlencode($aMailbox['SEARCH'][$iSetIndx][0]);
    $what = urlencode($aMailbox['SEARCH'][$iSetIndx][1]);
    $baseurl .= '&amp;where=' . $where .  '&amp;what=' .  $what;

    /* build thread sorting links */
    $newsort = $aMailbox['SORT'];
    if (sqimap_capability($imapConnection,'THREAD')) {
        if ($aMailbox['SORT'] & SQSORT_THREAD) {
            $newsort -= SQSORT_THREAD;
            $thread_name = _("Unthread View");
        } else {
            $thread_name = _("Thread View");
            $newsort = $aMailbox['SORT'] + SQSORT_THREAD;
        }
        $thread_link_str = '<small>[<a href="' . $baseurl . '&amp;srt='
            . $newsort . '&amp;startMessage=1">' . $thread_name
            . '</a>]</small>';
    } else {
        $thread_link_str ='';
    }
    $sort = $aMailbox['SORT'];

    /* FIX ME ADD CHECKBOX CONTROL. No checkbox => no buttons */



    /* future admin control over displayable buttons */

    $aAdminControl = array(
                           'markUnflagged' => 1,
                           'markFlagged'   => 1,
                           'markRead'      => 1,
                           'markUnread'    => 1,
                           'delete'        => 1,
                           'undeleteButton'=> 1,
                           'bypass_trash'  => 1,
                           'expungeButton' => 1,
                           'moveButton'    => 1,
                           'forward'       => 1
                           );
    /* user prefs control */
    $aUserControl = array (
                           'markUnflagged' => $show_flag_buttons,
                           'markFlagged'   => $show_flag_buttons,
                           'markRead'      => 1,
                           'markUnread'    => 1,
                           'delete'        => 1,
                           'undeleteButton'=> 1,
                           'bypass_trash'  => 1,
                           'expungeButton' => 1,
                           'moveButton'    => 1,
                           'forward'       => 1
                          );

    $showDelete = ($aMailbox['RIGHTS'] != 'READ-ONLY' &&
                   in_array('\\deleted',$aMailbox['PERMANENTFLAGS'], true)) ? true : false;
    $showByPassTrash = (!$aMailbox['AUTO_EXPUNGE'] && $aMailbox['RIGHTS'] != 'READ-ONLY' &&
                   in_array('\\deleted',$aMailbox['PERMANENTFLAGS'], true)) &&
                   $trash_folder ? true : false; //
    $showUndelete = (!$aMailbox['AUTO_EXPUNGE'] && $aMailbox['RIGHTS'] != 'READ-ONLY' &&
                   in_array('\\deleted',$aMailbox['PERMANENTFLAGS'], true) && !$trash_folder) ? true : false;
    $showMove   = ($aMailbox['RIGHTS'] != 'READ-ONLY') ? true : false;
    $showExpunge = (!$aMailbox['AUTO_EXPUNGE'] && $aMailbox['RIGHTS'] != 'READ-ONLY' &&
                   in_array('\\deleted',$aMailbox['PERMANENTFLAGS'], true)) ? true : false;
    $aImapControl = array (
                           'markUnflagged' => in_array('\\flagged',$aMailbox['PERMANENTFLAGS'], true),
                           'markFlagged'   => in_array('\\flagged',$aMailbox['PERMANENTFLAGS'], true),
                           'markRead'      => in_array('\\seen',$aMailbox['PERMANENTFLAGS'], true),
                           'markUnread'    => in_array('\\seen',$aMailbox['PERMANENTFLAGS'], true),
                           'delete'        => $showDelete,
                           'undeleteButton'=> $showUndelete,
                           'bypass_trash'  => $showByPassTrash,
                           'expungeButton' => $showExpunge,
                           'moveButton'    => $showMove,
                           'forward'       => 1
                          );
    $aButtonStrings = array(
                           'markUnflagged' => _("Unflag"),
                           'markFlagged'   => _("Flag"),
                           'markRead'      => _("Read"),
                           'markUnread'    => _("Unread"),
                           'delete'    => _("Delete"),
                           'undeleteButton'  => _("Undelete"),
                           'bypass_trash'  => _("Bypass Trash"),
                           'expungeButton' => _("Expunge"),
                           'moveButton'          => _("Move"),
                           'forward'       => _("Forward")
                           );
    /**
     * Register buttons in order to an array
     * The key is the "name", the first element of the value array is the "value", second argument is the type.
     */
    $aFormElements = array();
    foreach($aAdminControl as $k => $v) {
        if ($v & $aUserControl[$k] & $aImapControl[$k]) {
            switch ($k) {
              case 'markUnflagged':
              case 'markFlagged':
              case 'markRead':
              case 'markUnread':
              case 'delete':
              case 'undeleteButton':
              case 'expungeButton':
              case 'forward':
                $aFormElements[$k] = array($aButtonStrings[$k],'submit');
                break;
              case 'bypass_trash':
                $aFormElements[$k] = array($aButtonStrings[$k],'checkbox');
                break;
              case 'moveButton':
                $aFormElements['targetMailbox'] =
                   array(sqimap_mailbox_option_list($imapConnection, array(strtolower($lastTargetMailbox)), 0, $boxes),'select');
                $aFormElements['mailbox']       = array($aMailbox['NAME'],'hidden');
                $aFormElements['startMessage']  = array($aMailbox['PAGEOFFSET'],'hidden');
                $aFormElements[$k]              = array($aButtonStrings[$k],'submit');
                break;
            }
        }
        $aFormElements['account']  = array($iAccount,'hidden');
    }

    /*
    * This is the beginning of the message list table.
    * It wraps around all messages
    */
    $safe_name = preg_replace("/[^0-9A-Za-z_]/", '_', $aMailbox['NAME']);
    $form_name = "FormMsgs" . $safe_name;

    //if (!sqgetGlobalVar('align',$align,SQ_SESSION)) {
        $align = array('left' => 'left', 'right' => 'right');
    //}
    //sm_print_r($align);

    /* finally set the template vars */

    // FIX ME, before we support multiple templates we must review the names of the vars
    $aTemplate['color']     = $color;
    $aTemplate['form_name'] = "FormMsgs" . $safe_name;
    $aTemplate['form_id']   = 'mailbox_display_'.$iFormId;
    $aTemplate['page_selector'] = $page_selector;
    $aTemplate['page_selector_max'] = $page_selector_max;
    $aTemplate['messagesPerPage'] = $aMailbox['LIMIT'];
    $aTemplate['showall'] = $aMailbox['SHOWALL'][$iSetIndx];
    $aTemplate['end_msg'] = $iEnd;
    $aTemplate['align'] = $align;
    $aTemplate['iNumberOfMessages'] = $iNumberOfMessages;
    $aTemplate['aOrder'] = $aOrder;
    $aTemplate['aFormElements'] = $aFormElements;
    $aTemplate['sort'] = $sort;
    $aTemplate['pageOffset'] = $aMailbox['PAGEOFFSET'];
    $aTemplate['baseurl'] = $baseurl;
    $aTemplate['aMessages'] =& $aMessages;
    $aTemplate['trash_folder'] = $trash_folder;
    $aTemplate['sent_folder'] = $sent_folder;
    $aTemplate['draft_folder'] = $draft_folder;
    $aTemplate['thread_link_str'] = $thread_link_str;
    $aTemplate['php_self'] = $php_self;
    $aTemplate['mailbox'] = $sMailbox;
    $aTemplate['javascript_on'] = (isset($aProps['config']['javascript_on'])) ? $aProps['config']['javascript_on'] : false;
    $aTemplate['enablesort'] = (isset($aProps['config']['enablesort'])) ? $aProps['config']['enablesort'] : false;
    $aTemplate['icon_theme'] = (isset($aProps['config']['icon_theme'])) ? $aProps['config']['icon_theme'] : false;
    $aTemplate['use_icons'] = (isset($aProps['config']['use_icons'])) ? $aProps['config']['use_icons'] : false;
    $aTemplate['alt_index_colors'] = (isset($aProps['config']['alt_index_colors'])) ? $aProps['config']['alt_index_colors'] : false;

    return $aTemplate;
}


/**
* FIXME: Undocumented function
*/
function truncateWithEntities($subject, $trim_at)
{
    $ent_strlen = strlen($subject);
    if (($trim_at <= 0) || ($ent_strlen <= $trim_at))
        return $subject;

    global $languages, $squirrelmail_language;

    /*
    * see if this is entities-encoded string
    * If so, Iterate through the whole string, find out
    * the real number of characters, and if more
    * than $trim_at, substr with an updated trim value.
    */
    $trim_val = $trim_at;
    $ent_offset = 0;
    $ent_loc = 0;
    while ( $ent_loc < $trim_val && (($ent_loc = strpos($subject, '&', $ent_offset)) !== false) &&
            (($ent_loc_end = strpos($subject, ';', $ent_loc+3)) !== false) ) {
        $trim_val += ($ent_loc_end-$ent_loc);
        $ent_offset  = $ent_loc_end+1;
    }
    if (($trim_val > $trim_at) && ($ent_strlen > $trim_val) && (strpos($subject,';',$trim_val) < ($trim_val + 6))) {
        $i = strpos($subject,';',$trim_val);
        if ($i) {
            $trim_val = strpos($subject,';',$trim_val);
        }
    }
    // only print '...' when we're actually dropping part of the subject
    if ($ent_strlen <= $trim_val)
        return $subject;

    if (isset($languages[$squirrelmail_language]['XTRA_CODE']) &&
        function_exists($languages[$squirrelmail_language]['XTRA_CODE'])) {
        return $languages[$squirrelmail_language]['XTRA_CODE']('strimwidth', $subject, $trim_val);
    }

    return substr_replace($subject, '...', $trim_val);
}

/**
* This should go in imap_mailbox.php
* @param string $mailbox
*/
function handleAsSent($mailbox) {
    global $handleAsSent_result;

    /* First check if this is the sent or draft folder. */
    $handleAsSent_result = isSentMailbox($mailbox) || isDraftMailbox($mailbox);

    /* Then check the result of the handleAsSent hook. */
    do_hook('check_handleAsSent_result', $mailbox);

    /* And return the result. */
    return $handleAsSent_result;
}

/**
 * Process messages list form and handle the cache gracefully. If $sButton and
 * $aUid are provided as argument then you can fake a message list submit and
 * use it i.e. in read_body.php for del move next and update the cache
 *
 * @param  resource $imapConnection imap connection
 * @param  array $aMailbox (reference) cached mailbox
 * @param  string $sButton fake a submit button
 * @param  array  $aUid    fake the $msg array
 * @return string $sError error string in case of an error
 * @author Marc Groot Koerkamp
 */
function handleMessageListForm($imapConnection,&$aMailbox,$sButton='',$aUid = array()) {

    /* incoming formdata */
    $sButton = (sqgetGlobalVar('moveButton',      $sTmp, SQ_POST)) ? 'move'         : $sButton;
    $sButton = (sqgetGlobalVar('expungeButton',   $sTmp, SQ_POST)) ? 'expunge'      : $sButton;
    $sButton = (sqgetGlobalVar('attache',         $sTmp, SQ_POST)) ? 'attache'      : $sButton;
    $sButton = (sqgetGlobalVar('delete',          $sTmp, SQ_POST)) ? 'setDeleted'   : $sButton;
    $sButton = (sqgetGlobalVar('undeleteButton',  $sTmp, SQ_POST)) ? 'unsetDeleted'   : $sButton;
    $sButton = (sqgetGlobalVar('markRead',        $sTmp, SQ_POST)) ? 'setSeen'      : $sButton;
    $sButton = (sqgetGlobalVar('markUnread',      $sTmp, SQ_POST)) ? 'unsetSeen'    : $sButton;
    $sButton = (sqgetGlobalVar('markFlagged',     $sTmp, SQ_POST)) ? 'setFlagged'   : $sButton;
    $sButton = (sqgetGlobalVar('markUnflagged',   $sTmp, SQ_POST)) ? 'unsetFlagged' : $sButton;
    sqgetGlobalVar('targetMailbox', $targetMailbox,   SQ_POST);
    sqgetGlobalVar('bypass_trash',  $bypass_trash,    SQ_POST);
    sqgetGlobalVar('msg',           $msg,             SQ_POST);
    if (sqgetGlobalVar('account',       $iAccount,        SQ_POST) === false) {
        $iAccount = 0;
    }
    $sError = '';
    $mailbox = $aMailbox['NAME'];

    /* retrieve the check boxes */
    $aUid = (isset($msg) && is_array($msg)) ? array_values($msg) : $aUid;
    if (count($aUid) && $sButton != 'expunge') {
        $aUpdatedMsgs = false;
        $bExpunge = false;
        switch ($sButton) {
          case 'setDeleted':
            // check if id exists in case we come from read_body
            if (count($aUid) == 1 && is_array($aMailbox['UIDSET'][$aMailbox['SETINDEX']]) &&
                !in_array($aUid[0],$aMailbox['UIDSET'][$aMailbox['SETINDEX']])) {
                break;
            }
            // What kind of hook is this, can it be removed? Disabled for now because it can invalidate the cache
            //if (!boolean_hook_function('move_messages_button_action', NULL, 1)) {
                $aUpdatedMsgs = sqimap_msgs_list_delete($imapConnection, $mailbox, $aUid,$bypass_trash);
                $bExpunge = true;
            //}
            break;
          case 'unsetDeleted':
          case 'setSeen':
          case 'unsetSeen':
          case 'setFlagged':
          case 'unsetFlagged':
            // get flag
            $sFlag = (substr($sButton,0,3) == 'set') ? '\\'.substr($sButton,3) : '\\'.substr($sButton,5);
            $bSet  = (substr($sButton,0,3) == 'set') ? true : false;
            $aUpdatedMsgs = sqimap_toggle_flag($imapConnection, $aUid, $sFlag, $bSet, true);
            break;
          case 'move':
            $aUpdatedMsgs = sqimap_msgs_list_move($imapConnection,$aUid,$targetMailbox);
            sqsession_register($targetMailbox,'lastTargetMailbox');
            $bExpunge = true;
            break;
          case 'attache':
            $aMsgHeaders = array();
            foreach ($aUid as $iUid) {
                $aMsgHeaders[$iUid] = $aMailbox['MSG_HEADERS'][$iUid];
            }
            if (count($aMsgHeaders)) {
                $composesession = attachSelectedMessages($imapConnection,$aMsgHeaders);
                // dirty hack, add info to $aMailbox
                $aMailbox['FORWARD_SESSION'] = $composesession;
            }
            break;
        }
        /**
         * Updates messages is an array containing the result of the untagged
         * fetch responses send by the imap server due to a flag change. That
         * response is parsed in a array with msg arrays by the parseFetch function
         */
        if ($aUpdatedMsgs) {
            // Update the message headers cache
            $aDeleted = array();
            foreach ($aUpdatedMsgs as $iUid => $aMsg) {
                if (isset($aMsg['FLAGS'])) {
                    /**
                     * Only update the cached headers if the header is
                     * cached.
                     */
                    if (isset($aMailbox['MSG_HEADERS'][$iUid])) {
                        $aMailbox['MSG_HEADERS'][$iUid]['FLAGS'] = $aMsg['FLAGS'];
                    }
                    /**
                     * Count the messages with the \Delete flag set so we can determine
                     * if the number of expunged messages equals the number of flagged
                     * messages for deletion.
                     */
                    if (isset($aMsg['FLAGS']['\\deleted']) && $aMsg['FLAGS']['\\deleted']) {
                        $aDeleted[] = $iUid;
                    }
                }
            }
            if ($bExpunge && $aMailbox['AUTO_EXPUNGE'] &&
                $iExpungedMessages = sqimap_mailbox_expunge($imapConnection, $aMailbox['NAME'], true))
                {
                if (count($aDeleted) != $iExpungedMessages) {
                    // there are more messages deleted permanently then we expected
                    // invalidate the cache
                    $aMailbox['UIDSET'][$aMailbox['SETINDEX']] = false;
                    $aMailbox['MSG_HEADERS'] = false;
                } else {
                    // remove expunged messages from cache
                    $aUidSet = $aMailbox['UIDSET'][$aMailbox['SETINDEX']];
                    if (is_array($aUidSet)) {
                        // create a UID => array index temp array
                        $aUidSetDummy = array_flip($aUidSet);
                        foreach ($aDeleted as $iUid) {
                            // get the id as well in case of SQM_SORT_NONE
                            if ($aMailbox['SORT'] == SQSORT_NONE) {
                                $aMailbox['ID'] = false;
                                //$iId = $aMailbox['MSG_HEADERS'][$iUid]['ID'];
                                //unset($aMailbox['ID'][$iId]);
                            }
                            // unset the UID and message header
                            unset($aUidSetDummy[$iUid]);
                            unset($aMailbox['MSG_HEADERS'][$iUid]);
                        }
                        $aMailbox['UIDSET'][$aMailbox['SETINDEX']] = array_keys($aUidSetDummy);
                    }
                }
                // update EXISTS info
                if ($iExpungedMessages) {
                    $aMailbox['EXISTS'] -= (int) $iExpungedMessages;
                    $aMailbox['TOTAL'][$aMailbox['SETINDEX']] -= (int) $iExpungedMessages;
                }
                // Change the startMessage number if the mailbox was changed
                if (($aMailbox['PAGEOFFSET']+$iExpungedMessages-1) >= $aMailbox['EXISTS']) {
                    $aMailbox['PAGEOFFSET'] = ($aMailbox['PAGEOFFSET'] > $aMailbox['LIMIT']) ?
                        $aMailbox['PAGEOFFSET'] - $aMailbox['LIMIT'] : 1;
                }
            }
        }
    } else {
        if ($sButton == 'expunge') {
            /**
             * on expunge we do not know which messages will be deleted
             * so it's useless to try to sync the cache

             * Close the mailbox so we do not need to parse the untagged expunge
             * responses which do not contain uid info.
             * NB: Closing a mailbox is faster then expunge because the imap
             * server does not need to generate the untagged expunge responses
             */
            sqimap_run_command($imapConnection,'CLOSE',false,$result,$message);
            $aMailbox = sqm_api_mailbox_select($imapConnection,$iAccount, $aMailbox['NAME'],array(),array());
        } else {
            if ($sButton) {
                $sError = _("No messages were selected.");
            }
        }
    }
    return $sError;
}

function attachSelectedMessages($imapConnection,$aMsgHeaders) {
    global $username, $attachment_dir,
           $data_dir, $composesession,
           $compose_messages;

    if (!isset($compose_messages)) {
        $compose_messages = array();
        sqsession_register($compose_messages,'compose_messages');
    }

    if (!$composesession) {
        $composesession = 1;
        sqsession_register($composesession,'composesession');
    } else {
        $composesession++;
        sqsession_register($composesession,'composesession');
    }

    $hashed_attachment_dir = getHashedDir($username, $attachment_dir);

    $composeMessage = new Message();
    $rfc822_header = new Rfc822Header();
    $composeMessage->rfc822_header = $rfc822_header;
    $composeMessage->reply_rfc822_header = '';

    foreach($aMsgHeaders as $iUid => $aMsgHeader) {
        /**
         * Retrieve the full message
         */
        $body_a = sqimap_run_command($imapConnection, "FETCH $iUid RFC822", true, $response, $readmessage, TRUE);

        if ($response == 'OK') {
            $subject = (isset($aMsgHeader['SUBJECT'])) ? $aMsgHeader['SUBJECT'] : $iUid;

            array_shift($body_a);
            array_pop($body_a);
            $body = implode('', $body_a);
            $body .= "\r\n";

            $localfilename = GenerateRandomString(32, 'FILE', 7);
            $full_localfilename = "$hashed_attachment_dir/$localfilename";

            $fp = fopen( $full_localfilename, 'wb');
            fwrite ($fp, $body);
            fclose($fp);
            $composeMessage->initAttachment('message/rfc822',$subject.'.msg',
                 $full_localfilename);
        }
    }

    $compose_messages[$composesession] = $composeMessage;
    sqsession_register($compose_messages,'compose_messages');
    return $composesession;
}

?>
