<?php

/**
 * functions.php
 *
 * Functions for the Address Take plugin
 *
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package plugins
 * @subpackage abook_take
 */

/** */
function valid_email ($email, $verify) {
    global $Email_RegExp_Match;

    if (! eregi('^' . $Email_RegExp_Match . '$', $email)) {
        return false;
    }

    if (! $verify) {
        return true;
    }

    return checkdnsrr(substr(strstr($email, '@'), 1), 'ANY') ;
}

function abook_take_read_string($str) {
    global $abook_found_email, $Email_RegExp_Match;

    while (eregi('(' . $Email_RegExp_Match . ')', $str, $hits)) {
        $str = substr(strstr($str, $hits[0]), strlen($hits[0]));
        if (! isset($abook_found_email[$hits[0]])) {
            echo addHidden('email[]', $hits[0]);
            $abook_found_email[$hits[0]] = 1;
        }
    }

    return;
}

function abook_take_read_array($array) {
    foreach ($array as $item)
        abook_take_read_string($item->getAddress());
}

function abook_take_read() {
    global $message;

    echo '<br />' . addForm(SM_PATH . 'plugins/abook_take/take.php') .
         '<div style="text-align: center;">' . "\n";

    if (isset($message->rfc822_header->reply_to))
        abook_take_read_array($message->rfc822_header->reply_to);
    if (isset($message->rfc822_header->from))
        abook_take_read_array($message->rfc822_header->from);
    if (isset($message->rfc822_header->cc))
        abook_take_read_array($message->rfc822_header->cc);
    if (isset($message->rfc822_header->to))
        abook_take_read_array($message->rfc822_header->to);

    echo addSubmit(_("Take Address")) .
         '</div></form>';
}

function abook_take_pref() {
    global $username, $data_dir, $abook_take_verify;

    $abook_take_verify = getPref($data_dir, $username, 'abook_take_verify', false);
}

function abook_take_options() {
    global $optpage_data;

    $optpage_data['grps']['abook_take'] = _("Address Book Take");
    $optionValues = array();
    $optionValues[] = array(
        'name'    => 'abook_take_verify',
        'caption' => _("Try to verify addresses"),
        'type'    => SMOPT_TYPE_BOOLEAN,
        'refresh' => SMOPT_REFRESH_NONE
    );
    $optpage_data['vals']['abook_take'] = $optionValues;
}
