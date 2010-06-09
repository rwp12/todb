<?php

$unit = $_POST['show_jobs'];

      /*$unit_query = 'SELECT jobs_'.$yearval.'.*
                     FROM jobs_'.$yearval.' 
                     LEFT JOIN people_'.$yearval.' USING(uname) 
                     WHERE ((jobs_'.$yearval.'.deleted = FALSE) && (paper = "'. $unit.'"))'.$joborder;
      */
      // SELECT j.* FROM jobs_2008_09 as j LEFT JOIN people_2008_09 as p USING(uname) WHERE ((j.deleted = FALSE)  && (instr(concat(paper, ","), concat("PartII", ","))));
      
      /*
      $unit_query = 'SELECT jobs_'.$yearval.'.*
                     FROM jobs_'.$yearval.'
                     LEFT JOIN people_'.$yearval.' USING(uname)
                     WHERE ((jobs_'.$yearval.'.deleted = FALSE) && (instr(paper, "'. $unit.'")))'.$joborder;
      */
      
      // MJ, 2009-05-22: Added in the 'concat' bits in the where clause to require that the list of
      // units in a multiple-unit job are separated by commas.  This reduces the chances of a spurious
      // match (this was discovered when the unit 'PartII' also matched 'PartIII').
      
      $unit_joborder = ' order by type, term';
      
      $unit_query = 'SELECT j.* '.
                    'FROM jobs_'.$yearval.' as j '.
                    'LEFT JOIN people_'.$yearval.' as p USING(uname) '.
                    'WHERE ((j.deleted = FALSE) && (instr(concat(paper, ","), concat("'.$unit.'", ","))))'.$unit_joborder;


      //echo "<p>$unit_query</p>\n";

      $result = mysql_query($unit_query, $dbread) or die('Query failed: ' . mysql_error());
      
     // Added by MJ, 2009-05-06
     $firstheader = 1;

require('job_table.inc');

?>
