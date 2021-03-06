<?php
/**
 * us_ascii encoding functions
 *
 * takes a string of unicode entities and converts it to a us-ascii encoded string
 * Unsupported characters are replaced with ?.
 *
 * @version $Id$
 * @copyright Copyright &copy; 2004-2004 The SquirrelMail Project Team
 * @package squirrelmail
 * @subpackage encode
 */

/**
 * Converts string to us-ascii
 * @param string $string text with numeric unicode entities
 * @return string us-ascii encoded text
 */
function charset_encode_us_ascii ($string) {
   // don't run encoding function, if there is no encoded characters
   if (! preg_match("'&#[0-9]+;'",$string) ) return $string;

    $string=preg_replace("/&#([0-9]+);/e","unicodetousascii('\\1')",$string);
    // $string=preg_replace("/&#[xX]([0-9A-F]+);/e","unicodetousascii(hexdec('\\1'))",$string);

    return $string;
}

/**
 * Return us-ascii symbol when unicode character number is provided
 *
 * This function is used internally by charset_encode_us_ascii
 * function. It might be unavailable to other squirrelmail functions.
 * Don't use it or make sure, that functions/encode/us_ascii.php is
 * included.
 *
 * @param int $var decimal unicode value
 * @return string us-ascii character
 */
function unicodetousascii($var) {

    if ($var < 128) {
        $ret = chr ($var);
    } else {
        $ret='?';
    }
    return $ret;
}
?>