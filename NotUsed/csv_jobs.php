<?php

require('csvhdr.inc');
require('config/jobs.inc');
require('config/people.inc');
require('useful.inc');
require('config/config.inc');
require('config/db.inc');
require('config/years.inc');

if (get_exists('yearval') && ($_GET['yearval'] != '')) {

  $yearval = $_GET['yearval'];
  open_db_read();   

  if (get_exists('showemail')){ 
     $showemail = 'checked'; 
     $select_string .= ',people_'.$yearval.'.engid';
     $join_string = ' LEFT JOIN people_'.$yearval.' USING (uname,deleted)';
  } else {
     $showemail = '';
     $join_string = '';
  } 

  if (get_exists('filter_special')) {
     $filter_special = $_GET['filter_special'];

     $select_string = '*';

     require('view_jobs_special.inc');

  } else {

     $filter = '(jobs_'.$yearval.'.deleted = FALSE)';

     if (get_exists('year')) {
        $filter .= ' && (FALSE';
	foreach (str_split($_GET['year']) as $yearletter) {
           if ($yearletter != '?') {
              $filter .= " || (year = $yearletter)";
           } else {
              $filter .= " || (year < 1) || (year > 4) || (year IS NULL)";
           } 
        }
        $filter .= ')';
     }
     if (get_exists('group')) {
        $filter .= ' && (FALSE';
	foreach (str_split($_GET['group']) as $groupletter) {
           if ($yearletter != '?') {
              $filter .= " || (INSTR(prgroup, '$groupletter')";
           } else {
              if (get_exists('allgrps')) {
                 $allgrps=$_GET['allgrps'];
              } else {
                 $allgrps = '';
              } 
              $filter .= " || (select prgroup regexp '[^$allgrps]') || (prgroup is NULL)";
           } 
        }
        $filter .= ')';
     }
     if (get_exists('type')) {
	$filter .= ' && (FALSE';
	foreach (str_split($_GET['type']) as $typeletter) {
	   if ($typeletter != '?') {
	       $filter .= " || ( INSTR(type, '$typeletter')";
           } else {
               if (get_exists('alltypes')) {
                  $alltypes=$_GET['alltypes'];
               } else {
                  $alltypes = '';
               }
               $filter .= " || (select type regexp '[^$alltypes]') || (type is NULL)";
           }
        }
     }  

     if (get_exists('term')) {
	$filter .= ' && (FALSE';
	foreach (str_split($_GET['term']) as $termletter) {
	   if ($termletter != '?') {
	       $filter .= " || ( INSTR(term, '$termletter')";
           } else {
               if (get_exists('allterms')) {
                  $allterms=$_GET['allterms'];
               } else {
                  $allterms = '';
               }
               $filter .= " || (select term regexp '[^$allterms]') || (term is NULL)";
           }
        }
     }  

     if (get_exists('unalloc')) { $filter .= " && (uname <=> NULL)" ; }
     if (get_exists('modlead')) { $filter .= " && (INSTR(name, \"leader\") > 0) && NOT (paper IS NULL)" ; }

  }

  if (($result = mysql_query($full_query, $dbread)) === FALSE) {
     // ER_EMPTY_QUERY
     if (mysql_errno() == 1065) {
     } else {
       die('Query failed: ['.mysql_errno().'] ' . mysql_error() );
     }
  }

  $dopointsummary = FALSE;
  $dopastyearsummary = FALSE;
  // $jobssearchedfor = $var_person_uname;
  $notrunning = "";

  $csvmode = 1; 

  require('job_table.inc');

  mysql_free_result($result);

  close_db_read();

}	

