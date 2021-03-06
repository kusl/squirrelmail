<?php

/**
 * prefs.php
 *
 * Copyright (c) 1999-2002 The SquirrelMail Project Team
 * Licensed under the GNU GPL. For full terms see the file COPYING.
 *
 * This contains functions for manipulating user preferences
 *
 * $Id$
 */

global $prefs_are_cached, $prefs_cache;

if ( !session_is_registered('prefs_are_cached') ||
     !isset( $prefs_cache) ||
     !is_array( $prefs_cache) ||
     substr( phpversion(), 0, 3 ) == '4.1'  ) {
    $prefs_are_cached = false;
    $prefs_cache = array();
}

/**
 * Check the preferences into the session cache.
 */
function cachePrefValues($data_dir, $username) {
    global $prefs_are_cached, $prefs_cache;
       
    if ($prefs_are_cached) {
        return;
    }
    
    session_unregister('prefs_cache');
    session_unregister('prefs_are_cached');
    
    /* Calculate the filename for the user's preference file */
    $filename = getHashedFile($username, $data_dir, "$username.pref");

    /* A call to checkForPrefs here should take eliminate the need for */
    /* this to be called throughout the rest of the SquirrelMail code. */
    checkForPrefs($data_dir, $username, $filename);

    /* Make sure that the preference file now DOES exist. */
    if (!file_exists($filename)) {
        echo sprintf (_("Preference file, %s, does not exist. Log out, and log back in to create a default preference file."), $filename) . "<br>\n";
        exit;
    }

    $file = fopen($filename, 'r');

    /* Read in the preferences. */
    $highlight_num = 0;
    while (! feof($file)) {
        $pref = trim(fgets($file, 1024));
        $equalsAt = strpos($pref, '=');
        if ($equalsAt > 0) {
            $key = substr($pref, 0, $equalsAt);
            $value = substr($pref, $equalsAt + 1);
            if (substr($key, 0, 9) == 'highlight') {
                $key = 'highlight' . $highlight_num;
                $highlight_num ++;
            }

            if ($value != '') {
                $prefs_cache[$key] = $value;
            }
        }
     }
     fclose($file);

    $prefs_are_cached = true;

     session_register('prefs_cache');
     session_register('prefs_are_cached');
}
   
/**
 * Return the value for the prefernce given by $string.
 */
function getPref($data_dir, $username, $string, $default = '') {
    global $prefs_cache;
    $result = '';

    cachePrefValues($data_dir, $username);

    if (isset($prefs_cache[$string])) {
        $result = $prefs_cache[$string];
    } else {
        $result = $default;
    }

    return ($result);
}

/**
 * Save the preferences for this user.
 */
function savePrefValues($data_dir, $username) {
    global $prefs_cache;
   
    $filename = getHashedFile($username, $data_dir, "$username.pref");

    $file = fopen($filename, 'w');
    foreach ($prefs_cache as $Key => $Value) {
        if (isset($Value)) {
            fwrite($file, $Key . '=' . $Value . "\n");
        }
    }
    fclose($file);
}

/**
 * Remove a preference for the current user.
 */
function removePref($data_dir, $username, $string) {
    global $prefs_cache;

    cachePrefValues($data_dir, $username);
 
    if (isset($prefs_cache[$string])) {
        unset($prefs_cache[$string]);
    }
 
    savePrefValues($data_dir, $username);
}

/**
 * Set a there preference $string to $value.
 */
function setPref($data_dir, $username, $string, $value) {
    global $prefs_cache;

    cachePrefValues($data_dir, $username);
    if (isset($prefs_cache[$string]) && ($prefs_cache[$string] == $value)) {
        return;
    }

    if ($value === '') {
        removePref($data_dir, $username, $string);
        return;
    }

    $prefs_cache[$string] = $value;
    savePrefValues($data_dir, $username);
}

/**
 * Check for a preferences file. If one can not be found, create it.
 */
function checkForPrefs($data_dir, $username, $filename = '') {
    /* First, make sure we have the filename. */
    if ($filename == '') {
        $filename = getHashedFile($username, $data_dir, '$username.pref');
    }

    /* Then, check if the file exists. */
    if (!@file_exists($filename) ) {
        /* First, check the $data_dir for the default preference file. */
        $default_pref = $data_dir . 'default_pref';

        /* If it is not there, check the internal data directory. */
        if (!@file_exists($default_pref)) {
            $default_pref = '../data/default_pref';
        }

        /* Otherwise, report an error. */
        if (!file_exists($default_pref)) {
            echo _("Error opening ") . $default_pref . "<br>\n";
            echo _("Default preference file not found!") . "<br>\n";
            echo _("Please contact your system administrator and report this error.") . "<br>\n";
            exit;
        } else if (!@copy($default_pref, $filename)) {
            echo _("Error opening ") . $default_pref . '<br>';
            echo _("Could not create initial preference file!") . "<br>\n";
            echo _("Please contact your system administrator and report this error.") . "<br>\n";
            exit;
        }
    }
}

/**
 * Write the User Signature.
 */
function setSig($data_dir, $username, $value) {
    $filename = getHashedFile($username, $data_dir, "$username.sig");
    $file = fopen($filename, 'w');
    fwrite($file, $value);
    fclose($file);
}

/**
 * Get the signature.
 */
function getSig($data_dir, $username) {
    #$filename = $data_dir . $username . '.sig';
    $filename = getHashedFile($username, $data_dir, "$username.sig");
    $sig = '';
    if (file_exists($filename)) {
        $file = fopen($filename, 'r');
        while (!feof($file)) {
            $sig .= fgets($file, 1024);
        }
        fclose($file);
    }
    return $sig;
}

function getHashedFile($username, $dir, $datafile, $hash_search = true) {
    global $dir_hash_level;

    /* Remove trailing slash from $dir if found */
    if (substr($dir, -1) == '/') {
        $dir = substr($dir, 0, strlen($dir) - 1);
    }
    
    /* Compute the hash for this user and extract the hash directories. */
    $hash_dirs = computeHashDirs($username);

    /* First, get and make sure the full hash directory exists. */
    $real_hash_dir = getHashedDir($username, $dir, $hash_dirs);

    /* Set the value of our real data file. */
    $result = "$real_hash_dir/$datafile";

    /* Check for this file in the real hash directory. */
    if ($hash_search && !@file_exists($result)) {
        /* First check the base directory, the most common location. */
        if (@file_exists("$dir/$datafile")) {
            rename("$dir/$datafile", $result);

        /* Then check the full range of possible hash directories. */
        } else {
            $check_hash_dir = $dir;
            for ($h = 0; $h < 4; ++$h) {
                $check_hash_dir .= '/' . $hash_dirs[$h];
                if (@is_readable("$check_hash_dir/$datafile")) {
                    rename("$check_hash_dir/$datafile", $result);
                    break;
                }
            }
        }
    }
     
    /* Return the full hashed datafile path. */
    return ($result);
}

function getHashedDir($username, $dir, $hash_dirs = '') {
    global $dir_hash_level;

    /* Remove trailing slash from $dir if found */
    if (substr($dir, -1) == '/') {
        $dir = substr($dir, 0, strlen($dir) - 1);
    }
    
    /* If necessary, populate the hash dir variable. */
    if ($hash_dirs == '') {
        $hash_dirs = computeHashDirs($username);
    }

    /* Make sure the full hash directory exists. */
    $real_hash_dir = $dir;
    for ($h = 0; $h < $dir_hash_level; ++$h) {
        $real_hash_dir .= '/' . $hash_dirs[$h];
        if (!@is_dir($real_hash_dir)) {
            if (!@mkdir($real_hash_dir, 0770)) {
                echo sprintf(_("Error creating directory %s."), $real_hash_dir) . '<br>' .
                     _("Could not create hashed directory structure!") . "<br>\n" .
                     _("Please contact your system administrator and report this error.") . "<br>\n";
                exit;
            }
        }
    }

    /* And return that directory. */
    return ($real_hash_dir);
}

function computeHashDirs($username) {
    /* Compute the hash for this user and extract the hash directories. */
    $hash = base_convert(crc32($username), 10, 16);
    $hash_dirs = array();
    for ($h = 0; $h < 4; ++ $h) {
        $hash_dirs[] = substr($hash, $h, 1);
    }

    /* Return our array of hash directories. */
    return ($hash_dirs);
}

?>
