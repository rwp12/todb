<?php
require('csvhdr.inc');

$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());

$csvmode = 1; 

require('job_table.inc');
  
mysql_free_result($result);
mysql_free_result($gpresult);
mysql_free_result($divresult);

close_db_read();
?>
