<?php

/**
 * left_main.php
 *
 * Copyright (c) 1999-2005 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This is the code for the left bar. The left bar shows the folders
 * available, and has cookie information.
 *
 * @version $Id$
 * @package squirrelmail
 */

/**
 * Path for SquirrelMail required files.
 * @ignore
 */
define('SM_PATH','../');

/* SquirrelMail required files. */
require_once(SM_PATH . 'include/validate.php');
require_once(SM_PATH . 'functions/imap.php');
require_once(SM_PATH . 'functions/plugin.php');
require_once(SM_PATH . 'functions/page_header.php');
require_once(SM_PATH . 'functions/html.php');

/* These constants are used for folder stuff. */
define('SM_BOX_UNCOLLAPSED', 0);
define('SM_BOX_COLLAPSED',   1);

/* --------------------- FUNCTIONS ------------------------- */

function formatMailboxName($imapConnection, $box_array) {

    global $folder_prefix, $trash_folder, $sent_folder,
           $color, $move_to_sent, $move_to_trash,
           $unseen_notify, $unseen_type, $collapse_folders,
           $draft_folder, $save_as_draft,
           $use_special_folder_color;
    $real_box = $box_array['unformatted'];
    $mailbox = str_replace('&nbsp;','',$box_array['formatted']);
    $mailboxURL = urlencode($real_box);

    /* Strip down the mailbox name. */
    if (ereg("^( *)([^ ]*)$", $mailbox, $regs)) {
        $mailbox = $regs[2];
    }
    $unseen = 0;
    $status = array('','');
    if (($unseen_notify == 2 && $real_box == 'INBOX') ||
        $unseen_notify == 3) {
            $tmp_status = create_unseen_string($real_box, $box_array, $imapConnection, $unseen_type );
            if ($status !== false) {
                $status = $tmp_status;
            }
    }
    list($unseen_string, $unseen) = $status;
    $special_color = ($use_special_folder_color && isSpecialMailbox($real_box));

    /* Start off with a blank line. */
    $line = '';

    /* If there are unseen message, bold the line. */
    if ($unseen > 0) { $line .= '<b>'; }

    /* Create the link for this folder. */
    if ($status !== false) {
        $line .= '<a href="right_main.php?PG_SHOWALL=0&amp;sort=0&amp;startMessage=1&amp;mailbox='.
                 $mailboxURL.'" target="right" style="text-decoration:none">';
    }
    if ($special_color) {
        $line .= "<font color=\"$color[11]\">";
    }
    if ( $mailbox == 'INBOX' ) {
        $line .= _("INBOX");
    } else {
        $line .= str_replace(array(' ','<','>'),array('&nbsp;','&lt;','&gt;'),$mailbox);
    }
    if ($special_color == TRUE)
        $line .= '</font>';
    if ($status !== false) {
        $line .= '</a>';
    }

    /* If there are unseen message, close bolding. */
    if ($unseen > 0) { $line .= "</b>"; }

    /* Print unseen information. */
    if ($unseen_string != '') {
        $line .= "&nbsp;<small>$unseen_string</small>";
    }

    /* If it's the trash folder, show a purge link when needed */
    if (($move_to_trash) && ($real_box == $trash_folder)) {
        if (! isset($numMessages)) {
            $numMessages = sqimap_get_num_messages($imapConnection, $real_box);
        }

        if (($numMessages > 0) or ($box_array['parent'] == 1)) {
            $urlMailbox = urlencode($real_box);
            $line .= "\n<small>\n" .
                    '&nbsp;&nbsp;(<a href="empty_trash.php" style="text-decoration:none">'._("Purge").'</a>)' .
                    '</small>';
        }
    } else {
        $line .= concat_hook_function('left_main_after_each_folder',
                                      array(isset($numMessages) ? $numMessages : '',
                                            $real_box, $imapConnection));
    }

    /* Return the final product. */
    return ($line);
}

/**
 * Recursive function that computes the collapsed status and parent
 * (or not parent) status of this box, and the visiblity and collapsed
 * status and parent (or not parent) status for all children boxes.
 */
function compute_folder_children(&$parbox, $boxcount) {
    global $boxes, $data_dir, $username, $collapse_folders;
    $nextbox = $parbox + 1;

    /* Retreive the name for the parent box. */
    $parbox_name = $boxes[$parbox]['unformatted'];

    /* 'Initialize' this parent box to childless. */
    $boxes[$parbox]['parent'] = FALSE;

    /* Compute the collapse status for this box. */
    if( isset($collapse_folders) && $collapse_folders ) {
        $collapse = getPref($data_dir, $username, 'collapse_folder_' . $parbox_name);
        $collapse = ($collapse == '' ? SM_BOX_UNCOLLAPSED : $collapse);
    } else {
        $collapse = SM_BOX_UNCOLLAPSED;
    }
    $boxes[$parbox]['collapse'] = $collapse;

    /* Otherwise, get the name of the next box. */
    if (isset($boxes[$nextbox]['unformatted'])) {
        $nextbox_name = $boxes[$nextbox]['unformatted'];
    } else {
        $nextbox_name = '';
    }

    /* Compute any children boxes for this box. */
    while (($nextbox < $boxcount) &&
           (is_parent_box($boxes[$nextbox]['unformatted'], $parbox_name))) {

        /* Note that this 'parent' box has at least one child. */
        $boxes[$parbox]['parent'] = TRUE;

        /* Compute the visiblity of this box. */
        $boxes[$nextbox]['visible'] = ($boxes[$parbox]['visible'] &&
                                       ($boxes[$parbox]['collapse'] != SM_BOX_COLLAPSED));

        /* Compute the visibility of any child boxes. */
        compute_folder_children($nextbox, $boxcount);
    }

    /* Set the parent box to the current next box. */
    $parbox = $nextbox;
}

/**
 * Create the link for a parent folder that will allow that
 * parent folder to either be collapsed or expaned, as is
 * currently appropriate.
 */
function create_collapse_link($boxnum) {
    global $boxes, $imapConnection, $unseen_notify, $color;
    $mailbox = urlencode($boxes[$boxnum]['unformatted']);

    /* Create the link for this collapse link. */
    $link = '<a target="left" style="text-decoration:none" ' .
            'href="left_main.php?';
    if ($boxes[$boxnum]['collapse'] == SM_BOX_COLLAPSED) {
        $link .= "unfold=$mailbox\">+";
    } else {
        $link .= "fold=$mailbox\">-";
    }
    $link .= '</a>';

    /* Return the finished product. */
    return ($link);
}

/**
 * create_unseen_string:
 *
 * Create unseen and total message count for both this folder and
 * it's subfolders.
 *
 * @param string $boxName name of the current mailbox
 * @param array $boxArray array for the current mailbox
 * @param $imapConnection current imap connection in use
 * @return array[0] unseen message string (for display)
 * @return array[1] unseen message count
 */
function create_unseen_string($boxName, $boxArray, $imapConnection, $unseen_type) {
    global $boxes, $unseen_type, $color, $unseen_cum;

    /* Initialize the return value. */
    $result = array(0,0);

    /* Initialize the counts for this folder. */
    $boxUnseenCount = 0;
    $boxMessageCount = 0;
    $totalUnseenCount = 0;
    $totalMessageCount = 0;

    /* Collect the counts for this box alone. */
    $status = sqimap_status_messages($imapConnection, $boxName);
    $boxUnseenCount = $status['UNSEEN'];
    if ($boxUnseenCount === false) {
        return false;
    }
    if ($unseen_type == 2) {
        $boxMessageCount = $status['MESSAGES'];
    }

    /* Initialize the total counts. */

    if ($boxArray['collapse'] == SM_BOX_COLLAPSED && $unseen_cum) {
        /* Collect the counts for this boxes subfolders. */
        $curBoxLength = strlen($boxName);
        $boxCount = count($boxes);

        for ($i = 0; $i < $boxCount; ++$i) {
            /* Initialize the counts for this subfolder. */
            $subUnseenCount = 0;
            $subMessageCount = 0;

            /* Collect the counts for this subfolder. */
            if (($boxName != $boxes[$i]['unformatted'])
                    && (substr($boxes[$i]['unformatted'], 0, $curBoxLength) == $boxName)
                    && !in_array('noselect', $boxes[$i]['flags'])) {
                $status = sqimap_status_messages($imapConnection, $boxes[$i]['unformatted']);
                $subUnseenCount = $status['UNSEEN'];
                if ($unseen_type == 2) {
                    $subMessageCount = $status['MESSAGES'];;
                }
                /* Add the counts for this subfolder to the total. */
                $totalUnseenCount += $subUnseenCount;
                $totalMessageCount += $subMessageCount;
            }
        }

        /* Add the counts for all subfolders to that of the box. */
        $boxUnseenCount += $totalUnseenCount;
        $boxMessageCount += $totalMessageCount;
    }

    /* And create the magic unseen count string.     */
    /* Really a lot more then just the unseen count. */
    if (($unseen_type == 1) && ($boxUnseenCount > 0)) {
        $result[0] = "($boxUnseenCount)";
    } else if ($unseen_type == 2) {
        $result[0] = "($boxUnseenCount/$boxMessageCount)";
        $result[0] = "<font color=\"$color[11]\">$result[0]</font>";
    }

    /* Set the unseen count to return to the outside world. */
    $result[1] = $boxUnseenCount;

    /* Return our happy result. */
    return ($result);
}

/**
 * This simple function checks if a box is another box's parent.
 */
function is_parent_box($curbox_name, $parbox_name) {
    global $delimiter;

    /* Extract the name of the parent of the current box. */
    $curparts = explode($delimiter, $curbox_name);
    $curname = array_pop($curparts);
    $actual_parname = implode($delimiter, $curparts);
    $actual_parname = substr($actual_parname,0,strlen($parbox_name));

    /* Compare the actual with the given parent name. */
    return ($parbox_name == $actual_parname);
}


/* -------------------- MAIN ------------------------ */

/* get globals */
sqgetGlobalVar('username', $username, SQ_SESSION);
sqgetGlobalVar('key', $key, SQ_COOKIE);
sqgetGlobalVar('delimiter', $delimiter, SQ_SESSION);
sqgetGlobalVar('onetimepad', $onetimepad, SQ_SESSION);

sqgetGlobalVar('fold', $fold, SQ_GET);
sqgetGlobalVar('unfold', $unfold, SQ_GET);
sqgetGlobalVar('auto_create_done',$auto_create_done,SQ_SESSION);

/* end globals */

// open a connection on the imap port (143)
$imapConnection = sqimap_login($username, $key, $imapServerAddress, $imapPort, 10); // the 10 is to hide the output

/**
 * Using stristr since older preferences may contain "None" and "none".
 */
if (isset($left_refresh) && ($left_refresh != '') &&
    !stristr($left_refresh, 'none')){
    $xtra =  "\n<meta http-equiv=\"Expires\" content=\"Thu, 01 Dec 1994 16:00:00 GMT\" />\n" .
             "<meta http-equiv=\"Pragma\" content=\"no-cache\" />\n".
             "<meta http-equiv=\"REFRESH\" content=\"$left_refresh;URL=left_main.php\" />\n";
} else {
    $xtra = '';
}

/**
 * $advanced_tree is a boolean var which is set to default SM behaviour.
 * Setting $advanced tree to true causes SM to display a experimental
 * mailbox-tree with dhtml behaviour.
 * It only works on browsers which supports css and javascript. The used
 * javascript is experimental and doesn't support all browsers.
 * It has been tested on IE6 an Konquerer 3.0.0-2.
 **/

$advanced_tree = false; /* set this to true if you want to see a nicer mailboxtree */

if ($advanced_tree) {
$xtra .= <<<ECHO
<script language="Javascript" type="text/javascript">

<!--

    function hidechilds(el) {
        id = el.id+".0000";
        form_id = "mbx[" + el.id +"F]";
        if (document.all) {
            ele = document.all[id];
            if (ele) {
                if(ele.style.display == "none") {
                    ele.style.display = "block";
                    ele.style.visibility = "visible"
                        el.src="../images/minus.png";
                    document.all[form_id].value=0;
               } else {
                  ele.style.display = "none";
                  ele.style.visibility = "hidden"
                      el.src="../images/plus.png";
                  document.all[form_id].value=1;
               }
            }
        } else if (document.getElementById) {
            ele = document.getElementById(id);
                if (ele) {
                    if(ele.style.display == "none") {
                        ele.style.display = "block";
                        ele.style.visibility = "visible"
                            el.src="../images/minus.png";
                        document.getElementById(form_id).value=0;
                    } else {
                        ele.style.display = "none";
                        ele.style.visibility = "hidden"
                            el.src="../images/plus.png";
                        document.getElementById(form_id).value=1;
                    }
                }   
        }
    }

    function preload() {
       if (!document.images) return;
       var ar = new Array();
       var arguments = preload.arguments;
       for (var i = 0; i<arguments.length; i++) {
           ar[i] = new Image();
           ar[i].src = arguments[i];
       }
    }

    function buttonover(el,on) {
        if (!on) {
            el.style.borderColor="blue";
        } else {
            el.style.borderColor="orange";
        }
    }

    function buttonclick(el,on) {
        if (!on) { 
            el.style.border="groove"
        } else {
            el.style.border="ridge";
        }
    }

    function hideframe(hide) {
   
ECHO;
$xtra .= "      left_size = \"$left_size\";\n";
$xtra .= <<<ECHO
        if (document.all) {
            masterf = window.parent.document.all["fs1"];
            leftf = window.parent.document.all["left"];
            leftcontent = document.all["leftframe"];
            leftbutton = document.all["showf"];
        } else if (document.getElementById) {
            masterf = window.parent.document.getElementById("fs1");
            leftf = window.parent.document.getElementById("left");
            leftcontent = document.getElementById("leftframe");
            leftbutton = document.getElementById("showf");
        } else {
            return false;
        }
        if(hide) {
            new_col = calc_col("20");
            masterf.cols = new_col;
            document.body.scrollLeft=0;
            document.body.style.overflow='hidden';
            leftcontent.style.display = 'none';
            leftbutton.style.display='block';
        } else {
            masterf.cols = calc_col(left_size);
            document.body.style.overflow='';
            leftbutton.style.display='none';
            leftcontent.style.display='block';

      }
   }

   function calc_col(c_w) {

ECHO;
   if ($location_of_bar == 'right') {
       $xtra .= '     right=true;';
   } else {
       $xtra .= '     right=false;';
   }
   $xtra .= "\n";
$xtra .= <<<ECHO
     if (right) {
         new_col = '*,'+c_w;
     } else {
         new_col = c_w+',*';
     }
     return new_col;
   }

   function resizeframe(direction) {
     if (document.all) {
        masterf = window.parent.document.all["fs1"];
     } else if (document.getElementById) {
        window.parent.document.getElementById("fs1");
     } else {
        return false;
     }

ECHO;
   if ($location_of_bar == 'right') {
       $xtra .= '  colPat=/^\*,(\d+)$/;';
   } else {
       $xtra .= '  colPat=/^(\d+),.*$/;';
   }
   $xtra .= "\n";

$xtra .= <<<ECHO
     old_col = masterf.cols;
     colPat.exec(old_col);

     if (direction) {
        new_col_width = parseInt(RegExp.$1) + 25;

     } else {
        if (parseInt(RegExp.$1) > 35) {
           new_col_width = parseInt(RegExp.$1) - 25;
        }
     }
     masterf.cols = calc_col(new_col_width);
   }

//-->

</script>

ECHO;

/* style definitions */

$xtra .= <<<ECHO

<style type="text/css">
<!--
  body {
     margin: 0px 0px 0px 0px;
     padding: 5px 5px 5px 5px;
  }

  .button {
     border:outset;
     border-color: $color[9];
     background:$color[0];
     color:$color[6];
     width:99%;
     heigth:99%;
  }

  .mbx_par {
     font-size:1.0em;
     margin-left:4px;
     margin-right:0px;
     white-space: nowrap;
  }

  a.mbx_link {
      text-decoration: none;
      background-color: $color[0];
      display: inline;
  }

  a:hover.mbx_link {
      background-color: $color[9];
  }

  a.mbx_link img {
      border-style: none;
  }

  .mbx_sub {
     padding-left:5px;
     padding-right:0px;
     margin-left:4px;
     margin-right:0px;
     font-size:0.9em;
     white-space: nowrap;
  }

  .par_area {
     margin-top:0px;
     margin-left:4px;
     margin-right:0px;
     padding-left:10px;
     padding-bottom:5px;
     border-left: solid;
     border-left-width:0.1em;
     border-left-color:$color[9];
     border-bottom: solid;
     border-bottom-width:0.1em;
     border-bottom-color:$color[9];
     display: block;
  }

  .mailboxes {
     padding-bottom:3px;
     margin-right:4px;
     padding-right:4px;
     margin-left:4px;
     padding-left:4px;
     border: groove;
     border-width:0.1em;
     border-color:$color[9];
     background: $color[0];
  }

-->

</style>

ECHO;

}




displayHtmlHeader( 'SquirrelMail', $xtra );

/* If requested and not yet complete, attempt to autocreate folders. */
if ($auto_create_special && !$auto_create_done) {
    $autocreate = array($sent_folder, $trash_folder, $draft_folder);
    foreach( $autocreate as $folder ) {
        if (($folder != '') && ($folder != 'none')) {
            if ( !sqimap_mailbox_exists($imapConnection, $folder)) {
                sqimap_mailbox_create($imapConnection, $folder, '');
            } else if (!sqimap_mailbox_is_subscribed($imapConnection, $folder)) {
                sqimap_subscribe($imapConnection, $folder);
            }
        }
    }

    /* Let the world know that autocreation is complete! Hurrah! */
    $auto_create_done = TRUE;
    sqsession_register($auto_create_done, 'auto_create_done');
    /* retrieve the mailboxlist. We do this at a later stage again but if
       the right_frame loads faster then the second call retrieves a cached
       version of the mailboxlist without the newly created folders.
       The second parameter forces a non cached mailboxlist return.
     */
    if ($advanced_tree) {
        // do nothing, caching not seported yet.
    } else {
        $boxes = sqimap_mailbox_list($imapConnection,true);
    }
}

echo "\n<body bgcolor=\"$color[3]\" text=\"$color[6]\" link=\"$color[6]\" vlink=\"$color[6]\" alink=\"$color[6]\">\n";

do_hook('left_main_before');
if ($advanced_tree) {
   /* nice future feature, needs layout !! volunteers?   */
   $right_pos = $left_size - 20;
/*   echo '<div style="position:absolute;top:0;border=solid;border-width:0.1em;border-color:blue;"><div id="hidef" style="width=20;font-size:12"><a href="javascript:hideframe(true)"><b>&lt;&lt;</b></a></div>';
   echo '<div id="showf" style="width=20;font-size:12;display:none;"><a href="javascript:hideframe(false)"><b>&gt;&gt;</b></a></div>';
   echo '<div id="incrf" style="width=20;font-size:12"><a href="javascript:resizeframe(true)"><b>&gt;</b></a></div>';
   echo '<div id="decrf" style="width=20;font-size:12"><a href="javascript:resizeframe(false)"><b>&lt;</b></a></div></div>';
   echo '<div id="leftframe"><br /><br />';*/
}

echo "\n\n" . html_tag( 'table', '', 'left', '', 'border="0" cellspacing="0" cellpadding="0" width="99%"' ) .
    html_tag( 'tr' ) .
    html_tag( 'td', '', 'left' ) .
    html_tag( 'table', '', '', '', 'border="0" cellspacing="0" cellpadding="0"' ) .
    html_tag( 'tr' ) .
    html_tag( 'td', '', 'center' ) .
    '<font size="4"><b>'. _("Folders") . "</b><br /></font>\n\n";

if ($date_format != 6) {
    /* First, display the clock. */
    if ($hour_format == 1) {
        $hr = 'H:i';
        if ($date_format == 4) {
            $hr .= ':s';
        }
    } else {
        if ($date_format == 4) {
            $hr = 'g:i:s a';
        } else {
            $hr = 'g:i a';
        }
    }

    switch( $date_format ) {
    case 0:
        $clk = date('Y-m-d '.$hr. ' T', time());
        break;
    case 1:
        $clk = date('m/d/y '.$hr, time());
        break;
    case 2:
        $clk = date('d/m/y '.$hr, time());
        break;
    case 4:
    case 5:
        $clk = date($hr, time());
        break;
    default:
        $clk = getDayAbrv( date( 'w', time() ) ) . date( ', ' . $hr, time() );
    }
    $clk = str_replace(' ','&nbsp;',$clk);

    echo '<small><span style="white-space: nowrap;">' 
       . str_replace(' ', '&nbsp;', _("Last Refresh")) 
       . ":</span><br /><span style=\"white-space: nowrap;\">$clk</span></small><br />";
}

/* Next, display the refresh button. */
echo '<small style="white-space: nowrap;">(<a href="../src/left_main.php" target="left">'.
     _("Check mail") . '</a>)</small></td></tr></table><br />';

/* Lastly, display the folder list. */
if ( $collapse_folders ) {
    /* If directed, collapse or uncollapse a folder. */
    if (isset($fold)) {
        setPref($data_dir, $username, 'collapse_folder_' . $fold, SM_BOX_COLLAPSED);
    } else if (isset($unfold)) {
        setPref($data_dir, $username, 'collapse_folder_' . $unfold, SM_BOX_UNCOLLAPSED);
    }
}

sqgetGlobalVar('force_refresh',$force_refresh,SQ_GET);
if (!isset($boxes)) { // auto_create_done
    $boxes = sqimap_mailbox_list($imapConnection,$force_refresh);
}
/* Prepare do do out collapsedness and visibility computation. */
$curbox = 0;
$boxcount = count($boxes);

/* Compute the collapsedness and visibility of each box. */

while ($curbox < $boxcount) {
    $boxes[$curbox]['visible'] = TRUE;
    compute_folder_children($curbox, $boxcount);
}

for ($i = 0; $i < count($boxes); $i++) {
    if ( $boxes[$i]['visible'] ) {
        $mailbox = $boxes[$i]['formatted'];
	// remove folder_prefix using substr so folders aren't indented unnecessarily
        $mblevel = substr_count(substr($boxes[$i]['unformatted'], strlen($folder_prefix)), $delimiter) + 1;

        /* Create the prefix for the folder name and link. */
        $prefix = str_repeat('  ',$mblevel);
        if (isset($collapse_folders) && $collapse_folders && $boxes[$i]['parent']) {
            $prefix = str_replace(' ','&nbsp;',substr($prefix,0,strlen($prefix)-2)).
                      create_collapse_link($i) . '&nbsp;';
        } else {
            $prefix = str_replace(' ','&nbsp;',$prefix);
        }
        $line = "<span style=\"white-space: nowrap;\"><tt>$prefix</tt>";

        /* Add the folder name and link. */
        if (! isset($color[15])) {
            $color[15] = $color[6];
        }

        if (in_array('noselect', $boxes[$i]['flags'])) {
            if( isSpecialMailbox( $boxes[$i]['unformatted']) ) {
                $line .= "<font color=\"$color[11]\">";
            } else {
                $line .= "<font color=\"$color[15]\">";
            }
            if (ereg("^( *)([^ ]*)", $mailbox, $regs)) {
                $mailbox = str_replace('&nbsp;','',$mailbox);
                $line .= str_replace(' ', '&nbsp;', $mailbox);
            }
            $line .= '</font>';
        } else {
            $line .= formatMailboxName($imapConnection, $boxes[$i]);
        }

        /* Put the final touches on our folder line. */
        $line .= "</span><br />\n";

        /* Output the line for this folder. */
        echo $line;
    }
}

do_hook('left_main_after');
sqimap_logout($imapConnection);

?>
</td></tr></table>
</body></html>
