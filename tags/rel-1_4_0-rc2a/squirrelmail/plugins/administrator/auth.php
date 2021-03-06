<?php

/*
 *  This function tell other modules what users have access
 *  to the plugin.
 *  
 *  Philippe Mingo
 *  
 *  $Id$
 */

function adm_check_user() {
    global $PHP_SELF;
    require_once(SM_PATH . 'functions/global.php');
    
    if ( !sqgetGlobalVar('username',$username,SQ_SESSION) ) {
        $username = '';
    }

    /* This needs to be first, for all non_options pages */
    if (strpos('options.php', $PHP_SELF)) {
        $auth = FALSE;
    } else if (file_exists(SM_PATH . 'plugins/administrator/admins')) {
        $auths = file(SM_PATH . 'plugins/administrator/admins');
        $auth = in_array("$username\n", $auths);
    } else if (file_exists(SM_PATH . 'config/admins')) {
        $auths = file(SM_PATH . 'config/admins');
        $auth = in_array("$username\n", $auths);
    } else if ($adm_id = fileowner(SM_PATH . 'config/config.php')) {
        $adm = posix_getpwuid( $adm_id );
        $auth = ($username == $adm['name']);
    } else {
        $auth = FALSE;
    }

    return ($auth);
}

?>
