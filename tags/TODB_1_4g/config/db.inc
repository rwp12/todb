<?php

// MJ, 20090721:
// Please set database connection information here:
// ------------------------------------------------

// Database name (on localhost)
$database_host = 'localhost';
$database_name = '';

// read user details
$readuser_name = 'gen_todb_read';
$readuser_pass = '';

// write user details
$writeuser_name = 'gen_todb_wrt';
$writeuser_pass = '';



// ------------------------------------------
// include the database connection apparatus
// DO NOT remove this line!
// NOTE: this file is stored in the parent directory.
$path = substr($_SERVER['SCRIPT_FILENAME'], 0, strrpos($_SERVER['SCRIPT_FILENAME'], '/' ));
require ($path.'/dbconnect.inc');

?>
