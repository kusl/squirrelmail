<?php

/**
 * Name:   Spice of Life - Lite
 * Date:   October 20, 2001
 * Comment This theme generates random colors, featuring a
 *         lite background with dark text.
 *
 * @author Jorey Bump
 * @copyright &copy; 2000-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage themes
 */

/** Prevent direct script loading */
if (isset($_SERVER['SCRIPT_FILENAME']) && $_SERVER['SCRIPT_FILENAME'] == __FILE__) {
    die();
}

/** load sq_mt_randomize() */
include_once(SM_PATH . 'functions/strings.php');

/** seed the random number generator **/
sq_mt_randomize();

/** light(1) or dark(0) background? **/
$bg = mt_rand(0,1);

/** range delimiter **/
$bgrd = $bg * 128;

for ($i = 0; $i <= 15; $i++) {
    /** background/foreground toggle **/
    if ($i == 0 or $i == 3 or $i == 4 or $i == 5 or $i == 9 or $i == 10 or $i == 12) {
        /** background **/
        $cmin = 128;
        $cmax = 255;
    } else {
        /** text **/
        $cmin = 0;
        $cmax = 127;
    }

    /** generate random color **/
    $r = mt_rand($cmin,$cmax);
    $g = mt_rand($cmin,$cmax);
    $b = mt_rand($cmin,$cmax);

    /** set array element as hex string with hashmark (for HTML output) **/
    $color[$i] = sprintf('#%02X%02X%02X',$r,$g,$b);
}
