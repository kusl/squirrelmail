<?php
/**
 * decode/cp10029.php
 * $Id$
 *
 * Copyright (c) 2003 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This file contains cp10029 (MacLatin2) decoding function that 
 * is needed to read cp10029 encoded mails in non-cp10029 locale.
 * 
 * Original data taken from:
 *  ftp://ftp.unicode.org/Public/MAPPINGS/VENDORS/MICSFT/MAC/LATIN2.TXT
 *
 *  Name:     cp10029_MacLatin2 to Unicode table
 *  Unicode version: 2.0
 *  Table version: 2.00
 *  Table format:  Format A
 *  Date:          04/24/96
 *  Authors:       Lori Brownell <loribr@microsoft.com>
 *                 K.D. Chang    <a-kchang@microsoft.com>
 * @package squirrelmail
 * @subpackage decode
 */

/**
 * Decode cp10029 (MacLatin2) string
 * @param string $string Encoded string
 * @return string $string Decoded string
 */
function charset_decode_cp10029 ($string) {
    global $default_charset;

    if (strtolower($default_charset) == 'x-mac-centraleurroman')
        return $string;

    /* Only do the slow convert if there are 8-bit characters */
    /* avoid using 0xA0 (\240) in ereg ranges. RH73 does not like that */
    if (! ereg("[\200-\237]", $string) and ! ereg("[\241-\377]", $string) )
        return $string;

    $cp10029 = array(
	"\0x80" => '&#196;',
	"\0x81" => '&#256;',
	"\0x82" => '&#257;',
	"\0x83" => '&#201;',
	"\0x84" => '&#260;',
	"\0x85" => '&#214;',
	"\0x86" => '&#220;',
	"\0x87" => '&#225;',
	"\0x88" => '&#261;',
	"\0x89" => '&#268;',
	"\0x8A" => '&#228;',
	"\0x8B" => '&#269;',
	"\0x8C" => '&#262;',
	"\0x8D" => '&#263;',
	"\0x8E" => '&#233;',
	"\0x8F" => '&#377;',
	"\0x90" => '&#378;',
	"\0x91" => '&#270;',
	"\0x92" => '&#237;',
	"\0x93" => '&#271;',
	"\0x94" => '&#274;',
	"\0x95" => '&#275;',
	"\0x96" => '&#278;',
	"\0x97" => '&#243;',
	"\0x98" => '&#279;',
	"\0x99" => '&#244;',
	"\0x9A" => '&#246;',
	"\0x9B" => '&#245;',
	"\0x9C" => '&#250;',
	"\0x9D" => '&#282;',
	"\0x9E" => '&#283;',
	"\0x9F" => '&#252;',
	"\0xA0" => '&#8224;',
	"\0xA1" => '&#176;',
	"\0xA2" => '&#280;',
	"\0xA3" => '&#163;',
	"\0xA4" => '&#167;',
	"\0xA5" => '&#8226;',
	"\0xA6" => '&#182;',
	"\0xA7" => '&#223;',
	"\0xA8" => '&#174;',
	"\0xA9" => '&#169;',
	"\0xAA" => '&#8482;',
	"\0xAB" => '&#281;',
	"\0xAC" => '&#168;',
	"\0xAD" => '&#8800;',
	"\0xAE" => '&#291;',
	"\0xAF" => '&#302;',
	"\0xB0" => '&#303;',
	"\0xB1" => '&#298;',
	"\0xB2" => '&#8804;',
	"\0xB3" => '&#8805;',
	"\0xB4" => '&#299;',
	"\0xB5" => '&#310;',
	"\0xB6" => '&#8706;',
	"\0xB7" => '&#8721;',
	"\0xB8" => '&#322;',
	"\0xB9" => '&#315;',
	"\0xBA" => '&#316;',
	"\0xBB" => '&#317;',
	"\0xBC" => '&#318;',
	"\0xBD" => '&#313;',
	"\0xBE" => '&#314;',
	"\0xBF" => '&#325;',
	"\0xC0" => '&#326;',
	"\0xC1" => '&#323;',
	"\0xC2" => '&#172;',
	"\0xC3" => '&#8730;',
	"\0xC4" => '&#324;',
	"\0xC5" => '&#327;',
	"\0xC6" => '&#8710;',
	"\0xC7" => '&#171;',
	"\0xC8" => '&#187;',
	"\0xC9" => '&#8230;',
	"\0xCA" => '&#160;',
	"\0xCB" => '&#328;',
	"\0xCC" => '&#336;',
	"\0xCD" => '&#213;',
	"\0xCE" => '&#337;',
	"\0xCF" => '&#332;',
	"\0xD0" => '&#8211;',
	"\0xD1" => '&#8212;',
	"\0xD2" => '&#8220;',
	"\0xD3" => '&#8221;',
	"\0xD4" => '&#8216;',
	"\0xD5" => '&#8217;',
	"\0xD6" => '&#247;',
	"\0xD7" => '&#9674;',
	"\0xD8" => '&#333;',
	"\0xD9" => '&#340;',
	"\0xDA" => '&#341;',
	"\0xDB" => '&#344;',
	"\0xDC" => '&#8249;',
	"\0xDD" => '&#8250;',
	"\0xDE" => '&#345;',
	"\0xDF" => '&#342;',
	"\0xE0" => '&#343;',
	"\0xE1" => '&#352;',
	"\0xE2" => '&#8218;',
	"\0xE3" => '&#8222;',
	"\0xE4" => '&#353;',
	"\0xE5" => '&#346;',
	"\0xE6" => '&#347;',
	"\0xE7" => '&#193;',
	"\0xE8" => '&#356;',
	"\0xE9" => '&#357;',
	"\0xEA" => '&#205;',
	"\0xEB" => '&#381;',
	"\0xEC" => '&#382;',
	"\0xED" => '&#362;',
	"\0xEE" => '&#211;',
	"\0xEF" => '&#212;',
	"\0xF0" => '&#363;',
	"\0xF1" => '&#366;',
	"\0xF2" => '&#218;',
	"\0xF3" => '&#367;',
	"\0xF4" => '&#368;',
	"\0xF5" => '&#369;',
	"\0xF6" => '&#370;',
	"\0xF7" => '&#371;',
	"\0xF8" => '&#221;',
	"\0xF9" => '&#253;',
	"\0xFA" => '&#311;',
	"\0xFB" => '&#379;',
	"\0xFC" => '&#321;',
	"\0xFD" => '&#380;',
	"\0xFE" => '&#290;',
	"\0xFF" => '&#711;'
   );

    $string = str_replace(array_keys($cp10029), array_values($cp10029), $string);

    return $string;
}
?>
