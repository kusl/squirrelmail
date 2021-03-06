<?php
/**
 * SquirrelMail internal gettext functions
 *
 * Uses php-gettext classes
 * @copyright (c) 1999-2004 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public Licens
 * @link http://www.php.net/gettext Original php gettext manual
 * @link http://savannah.nongnu.org/projects/php-gettext php-gettext classes
 * @version $Id$
 * @package squirrelmail
 * @subpackage i18n
 */

/** Almost everything requires global.php... */
require_once(SM_PATH . 'functions/global.php');

/** Load classes and other functions */
include_once(SM_PATH . 'class/l10n.class.php');
include_once(SM_PATH . 'functions/ngettext.php');

/**
 * Alternative php gettext function (short form)
 *
 * @link http://www.php.net/function.gettext
 *
 * @param string $str English string
 * @return string translated string
 */
function _($str) {
    global $l10n, $gettext_domain;
    if ($l10n[$gettext_domain]->error==1) return $str;
    return $l10n[$gettext_domain]->translate($str);
}

/**
 * Alternative php bindtextdomain function
 *
 * Sets path to directory containing domain translations
 *
 * @link http://www.php.net/function.bindtextdomain
 * @param string $domain gettext domain name
 * @param string $dir directory that contains all translations
 * @return string path to translation directory
 */
function bindtextdomain($domain, $dir) {
    global $l10n, $sm_notAlias;
    if (substr($dir, -1) != '/') $dir .= '/';
    $mofile=$dir . $sm_notAlias . '/LC_MESSAGES/' . $domain . '.mo';

    $input = new FileReader($mofile);
    $l10n[$domain] = new gettext_reader($input);

    return $dir;
}

/**
 * Alternative php textdomain function
 *
 * Sets default domain name
 *
 * @link http://www.php.net/function.textdomain
 * @param string $name gettext domain name
 * @return string gettext domain name
 */
function textdomain($name = false) {
    global $gettext_domain;
    if ($name) $gettext_domain=$name;
    return $gettext_domain;
}
?>