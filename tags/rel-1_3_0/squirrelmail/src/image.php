<?php

/**
 * image.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file shows an attached image
 *
 * $Id$
 */

require_once('../src/validate.php');
require_once('../functions/date.php');
require_once('../functions/page_header.php');
require_once('../src/load_prefs.php');
require_once('../functions/html.php');

displayPageHeader($color, 'None');

if (isset($where) && isset($what)) {
  // from a search
  $ViewMessageLink = '<a href="../src/read_body.php?mailbox=' . urlencode($mailbox) .
        '&amp;passed_id=' . $passed_id . '&amp;where=' . urlencode($where) . 
        '&amp;what=' . urlencode($what). '">' . _("View message") . '</a>';
} else {   
  $ViewMessageLink = '<a href="../src/read_body.php?mailbox=' . urlencode($mailbox) .
       '&amp;passed_id=' . $passed_id . '&amp;startMessage=' . $startMessage .
       '&amp;show_more=0">' . _("View message") . '</a>';
}

$DownloadLink = '../src/download.php?passed_id=' . $passed_id .
               '&amp;mailbox=' . urlencode($mailbox) . 
               '&amp;passed_ent_id=' . $passed_ent_id . '&amp;absolute_dl=true';

echo '<br>' . 
    html_tag( 'table',
        html_tag( 'tr',
        html_tag( 'td',
                '<b>' .
                _("Viewing an image attachment") . ' - ' .
                $ViewMessageLink . '</b>' ,
          'center', $color[0] )
         ) .
        html_tag( 'tr',
        html_tag( 'td',
                    '<a href="' . $DownloadLink . '">' .
		    _("Download this as a file") .
                    '</a><br>&nbsp;' . "\n" ,
          'center' )
         ) ,
     'center', '', 'border="0" width="100%" cellspacing="0" cellpadding="2"' ) .

    html_tag( 'table',
        html_tag( 'tr',
        html_tag( 'td',
                '<img src="' . $DownloadLink . '">'
        , 'left', $color[4] )
         )
   , 'center', '', 'border="0" cellspacing="0" cellpadding="2"' );

?>
