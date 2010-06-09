<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
require('config/jobs.inc');
require('locks.inc');

$yearval = $_REQUEST["yearval"];

// temporary - disable the Edit button
$isadminuser = FALSE;

//$postvars = explode(';',$_POST['show_related_items_button']);
// schema for postvars: thing to match, value to match, things to display (plural), (singular), 
//$tablename = $postvars[2];
//$tablething = $postvars[3];
//$thing = $postvars[0];
//$value = $postvars[1];

$thing = $_REQUEST['show_related_thing'];
$value = $_REQUEST['show_related_items_button'];

$tablename = "jobs";
$tablething = 'Job';

$full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE (deleted=FALSE) && '.$thing.'="'.$value.'" ORDER BY paper';

require('common1.inc');
require('config/header.inc');
require('config/top.inc');
require('common2.inc');

$result = mysql_query($full_query, $dbread) or die('Query failed: ' . mysql_error());
$do_buttons_in_job_table = 'FALSE';
require('job_table.inc');

// Free resultsets
mysql_free_result($result);
// Closing connection

close_db_read();

require('config/footer.inc');

?> 
