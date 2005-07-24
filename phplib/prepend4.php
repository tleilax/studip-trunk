<?php
/*
 * Session Management for PHP3
 *
 * Copyright (c) 1998,1999 SH Online Dienst GmbH
 *                    Boris Erdmann, Kristian Koehntopp
 *
 * $Id$
 *
 */ 
(!isset($_REQUEST['GLOBALS'])) OR die('Setting the $GLOBALS array is not tolerated!');
$_PHPLIB = array();
$_PHPLIB["libdir"] = ""; 

define('PHPLIB_SESSIONDATA_TABLE', 'session_data');
define('PHPLIB_USERDATA_TABLE', 'user_data');

require($_PHPLIB["libdir"] . "db_mysql.inc");  /* Change this to match your database. */
require($_PHPLIB["libdir"] . "ct_sql_studip.inc");    /* Change this to match your data storage container */
require($_PHPLIB["libdir"] . "session4_custom.inc");   /* Required for everything below.      */
require($_PHPLIB["libdir"] . "auth4.inc");      /* Disable this, if you are not using authentication. */
require($_PHPLIB["libdir"] . "perm.inc");      /* Disable this, if you are not using permission checks. */
require($_PHPLIB["libdir"] . "user4.inc");      /* Disable this, if you are not using per-user variables. */


/* Additional require statements go below this line */

require($_PHPLIB["libdir"] . "email_validation.inc");	/* Required, contains register-check functions. */
require($_PHPLIB["libdir"] . "smtp.inc");             /* Required, contains email functions */

/* Additional require statements go before this line */

require($_PHPLIB["libdir"] . "local.inc");     /* Required, contains your local configuration. */

require($_PHPLIB["libdir"] . "page4.inc");      /* Required, contains the page management functions. */

?>
