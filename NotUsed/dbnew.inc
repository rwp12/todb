<?php

$dbread = NULL;
$dbwrite = NULL; 

function open_db_read() {

   global $dbread;

   $dbread = mysql_connect('localhost', 'chem_todb_read', 'neigaeYaoth8cae')
      or die('Could not connect: ' . mysql_error());
   mysql_select_db('chem_todb', $dbread);

}

function close_db_read() {

   global $dbread;
   mysql_close($dbread);

}

function open_db_write() {

   global $dbwrite;

   $dbwrite = mysql_connect('localhost', 'chem_todb_wrt', 'eiZohsh3meiphoh') 
      or die('Could not connect: ' . mysql_error());
   mysql_select_db('chem_todb', $dbwrite);

}

function close_db_write() {

   global $dbwrite;
   mysql_close($dbwrite);

}

?>
