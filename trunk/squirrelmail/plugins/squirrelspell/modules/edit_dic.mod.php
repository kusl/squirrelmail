<?php

   /**
    **  EDIT_DIC.MOD.PHP -- Squirrelspell module
    **
    **  Copyright (c) 1999-2001 The SquirrelMail development team
    **  Licensed under the GNU GPL. For full terms see the file COPYING.
    **
    **   This module handles the encryption/decryption of the user dictionary
    **   if the user so chooses from the options page.
    **
    **  $Id$
    **/

    // fidian, you owe me a pack of Guinness! :)
    global $color;
    $words=sqspell_getWords();
    if (!$words){
        // Agt. Smith: "You're empty."
        // Neo: "So are you."
        sqspell_makePage(_("Personal Dictionary"), null, '<p>' . _("No words in your personal dictionary.") . '</p>');
    } else {
        // We're loaded with booty.
        $pre_msg = '<p>' . _("Please check any words you wish to delete from your dictionary.") . "</p>\n";
        $pre_msg .= "<table border=\"0\" width=\"95%\" align=\"center\">\n";
        $langs=sqspell_getSettings($words);
        for ($i=0; $i<sizeof($langs); $i++){
            $lang_words = sqspell_getLang($words, $langs[$i]);
            if ($lang_words){
                // No words in this dictionary.
                if (!$msg) {
                    $msg = $pre_msg;
                }
                $msg .= "<tr bgcolor=\"$color[0]\" align=\"center\"><th>" . 
                        sprintf( _("%s dictionary"), $langs[$i] ) . '</th></tr>'.
                        '<tr><td align="center">'.
                        '<form method="post">'.
                        '<input type="hidden" name="MOD" value="forget_me">'.
                        '<input type="hidden" name="sqspell_use_app" value="' . $langs[$i] . '">'.
                        '<table border="0" width="95%" align="center">'.
                        '<tr>'.
                        "<td valign=\"top\">\n";
                $words_ary=explode("\n", $lang_words);
                array_pop($words_ary);
                array_shift($words_ary);
                // Do some fancy stuff to separate the words into three columns.
                for ($j=0; $j<sizeof($words_ary); $j++){
                    if ($j==intval(sizeof($words_ary)/3) || $j==intval(sizeof($words_ary)/3*2))
                	$msg .= "</td><td valign=\"top\">\n";
                    $msg .= "<input type=\"checkbox\" name=\"words_ary[]\" value=\"$words_ary[$j]\"> $words_ary[$j]<br>";
                }
                $msg .= '</td></tr></table></td></tr>' .
                        "<tr bgcolor=\"$color[0]\" align=\"center\"><td>".
                        '<input type="submit" value="' . _("Delete checked words") . '"></form>'.
                        '</td></tr><tr><td><hr>'.
                        "</td></tr>\n";
            }
        }
        // Check if all dictionaries were empty.
        if (!$msg) {
            $msg = '<p>' . _("No words in your personal dictionary.") . '</p>';
        } else {
            $msg .= '</table>';
        }
        sqspell_makePage(_("Edit your Personal Dictionary"), null, $msg);
    }

?>