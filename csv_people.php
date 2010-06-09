<?php

require('csvhdr.inc');
require('config/jobs.inc');
require('config/people.inc');
require('useful.inc');
require('config/config.inc');
require('config/db.inc');
require('config/years.inc');

if (get_exists('yearval') && get_exists('show_jobs') &&
    ($_GET['yearval'] != '') && ($_GET['show_jobs'] != '')) {

  $yearval = $_GET['yearval'];
  $var_person_uname = $_GET['show_jobs'];

  open_db_read();   

  $full_query = 'SELECT * FROM jobs_'.$yearval.' WHERE ((deleted = FALSE) && (uname = "' . $var_person_uname . '")) ORDER BY year, prgroup, paper, name';
  if (($result = mysql_query($full_query, $dbread)) === FALSE) {
     // ER_EMPTY_QUERY
     if (mysql_errno() == 1065) {
     } else {
       die('Query failed: ['.mysql_errno().'] ' . mysql_error() );
     }
  }

  $dopointsummary = FALSE;
  $dopastyearsummary = FALSE;
  $jobssearchedfor = $var_person_uname;
  $notrunning = "";

  $csvmode = 1; 

  require('job_table.inc');

  mysql_free_result($result);

  close_db_read();

}	
