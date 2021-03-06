<?php
/*
 * Created by SquirrelMail Development Team
 * Place site/location specific config vars in this config file
 * $Id$
 */

define('PATH','/path/to/wtfw/');          // '/' terminated absolute path
define('WTFW_DIFF_TEMP','/path/to/tmp/'); // '/' terminated absolute path
define('URI_PATH','/wtfw/');
define('FILENAME', URI_PATH . 'index.php'); // filename used as base document
                                            // -- ABSOLUTE page reference as seen by webserver

/* database settings */
define('DATABASE', 'mysql'); // database type (MySQL is the only DB module at the moment)
define('DBHOST', 'localhost'); // database IP address
define('DBNAME', 'wtf'); // database name
define('DBUSER', 'root'); // database username
define('DBPASS', ''); // database password

/* debugging */
define('DEBUG', FALSE); // show debug information
define('DEBUG_VAR', TRUE); // show variable debug information
define('DEBUG_TRACE', TRUE); // show trace debug information
define('DEBUG_SQL', TRUE); // show SQL debug information
define('DEBUG_TIME', TRUE); // show execution time
define('RENDER', TRUE); // use rendering engine (FALSE bypasses rendering phase)

?>
