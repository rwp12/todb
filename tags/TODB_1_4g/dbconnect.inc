<?php

$dbread = NULL;
$dbwrite = NULL;
// MJ, July 2009: necessary to connect to information_schema table to
// retrieve table comments, etc
$db_metadata = NULL;

function open_db_read() {

   global $dbread, $db_metadata, $database_host, $database_name, $readuser_name, $readuser_pass;

   // connect to DB's information schema:
   // MJ, July 2009: necessary to connect to information_schema table to
   // retrieve table comments, etc
   $db_metadata = mysql_connect($database_host, $readuser_name, $readuser_pass, true)
      or die('Could not connect: ' . mysql_error());
   mysql_select_db('information_schema', $db_metadata);

   // connect to DB via read user
   $dbread = mysql_connect('localhost', $readuser_name, $readuser_pass, true)
      or die('Could not connect: ' . mysql_error());
   mysql_select_db($database_name, $dbread);
}

function close_db_read() {

   global $dbread, $db_metadata;
   mysql_close($dbread);
   mysql_close($db_metadata);


}

function open_db_write() {

   global $dbwrite, $database_host, $database_name, $writeuser_name, $writeuser_pass;

   $dbwrite = mysql_connect($database_host, $writeuser_name, $writeuser_pass)
      or die('Could not connect: ' . mysql_error());
   mysql_select_db($database_name, $dbwrite);

}

function close_db_write() {

   global $dbwrite;
   mysql_close($dbwrite);

}


?>
