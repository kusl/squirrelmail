<?php
/**
 * util_addressbook.php
 *
 * Functions to make working with address books easier
 * 
 * @copyright &copy; 1999-2007 The SquirrelMail Project Team
 * @license http://opensource.org/licenses/gpl-license.php GNU Public License
 * @version $Id$
 * @package squirrelmail
 * @subpackage templates
 */

/**
 * Display a column header with sort buttons
 * 
 * @param string $field which field to display
 * @author Steve Brown
 * @since 1.5.2
 */
function addAbookSort ($field) {
    global $abook_sort_order;
    
    switch ($field) {
        case 'nickname':
            $str = _("Nickname");
            $alt = _("sort by nickname");
            $down = 0;
            $up = 1;
            $has_sort = true;
            break;
        case 'fullname':
            $str = _("Name");
            $alt = _("sort by name");
            $down = 2;
            $up = 3;
            $has_sort = true;
            break;
        case 'email':
            $str = _("E-mail");
            $alt = _("sort by email");
            $down = 4;
            $up = 5;
            $has_sort = true;
            break;
        case 'info':
            $str = _("Info");
            $alt = _("sort by info");
            $down = 6;
            $up = 7;
            $has_sort = true;
            break;
        default:
            return 'BAD SORT FIELD GIVEN: "'.$field.'"';
    }
    
    return $str . ($has_sort ? show_abook_sort_button($abook_sort_order, $alt, $down, $up) : '');
}

/**
 * Create a link to compose an email to the email address given.
 * 
 * @param array $row contact as given to the addressbook_list.tpl template
 * @author Steve Brown
 * @since 1.5.2
 */
function composeLink ($row) {
    return makeComposeLink('src/compose.php?send_to=' .
                           rawurlencode($row['FullAddress']),
                           htmlspecialchars($row['Email']));
}

/**
 * Format the address book into a format that is easy for template authors
 * to use
 * 
 * @param array $addresses all contacts as given by calling $abook->list_addr()
 * @return array
 * @author Steve Brown
 * @since 1.5.2
 */
function formatAddressList ($addresses) {
    if (!is_array($addresses) || count($addresses) == 0)
        return array();
        
    $contacts = array();
    while(list($undef,$row) = each($addresses)) {
        $contact = array (
                            'FirstName'     => htmlspecialchars($row['firstname']),
                            'LastName'      => htmlspecialchars($row['lastname']),
                            'FullName'      => htmlspecialchars($row['name']),
                            'NickName'      => htmlspecialchars($row['nickname']),
                            'Email'         => htmlspecialchars($row['email']),
                            'FullAddress'   => htmlspecialchars(AddressBook::full_address($row)),
                            'Info'          => htmlspecialchars($row['label']),
                            'Extra'         => (isset($row['extra']) ? $row['extra'] : NULL),
                            'Source'        => htmlspecialchars($row['source']),
                            'JSEmail'       => htmlspecialchars(addcslashes(AddressBook::full_address($row), "'"), ENT_QUOTES),
                         );
        $contacts[] = $contact;
    }
    
    return $contacts;
}

/**
 * Function to include JavaScript code
 * @return void
 */
function insert_javascript() {
    ?>
    <script type="text/javascript"><!--

    function to_and_close($addr) {
        to_address($addr);
        parent.close();
    }

    function to_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to.value =
                    parent.opener.document.compose.send_to.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to.value = $addr;
            }
        }
    }

    function cc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_cc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_cc.value =
                    parent.opener.document.compose.send_to_cc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_cc.value = $addr;
            }
        }
    }

    function bcc_address($addr) {
        var prefix    = "";
        var pwintype = typeof parent.opener.document.compose;

        $addr = $addr.replace(/ {1,35}$/, "");

        if (pwintype != "undefined") {
            if (parent.opener.document.compose.send_to_bcc.value) {
                prefix = ", ";
                parent.opener.document.compose.send_to_bcc.value =
                    parent.opener.document.compose.send_to_bcc.value + ", " + $addr;
            } else {
                parent.opener.document.compose.send_to_bcc.value = $addr;
            }
        }
    }

// --></script>
<?php
} /* End of included JavaScript */

/**
 * Function to build a list of available backends for searching
 * 
 * @return array
 * @author Steve Brown
 * @since 1.5.2
 */
function getBackends () {
    global $abook;
    
    $backends = array();
    $backends['-1'] = _("All address books");
    $ret = $abook->get_backend_list();
    while (list($undef,$v) = each($ret)) {
        $backends[$v->bnum] = $v->sname;
    }
    
    return $backends;
}
?>