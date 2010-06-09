<?php
   if ($filter_special == "filter_nonperson") {

        # This does a left join, with "USING (uname)".  It works because if 
        # there's no matching uname, it fills the columns for the second 
        # table with nulls in the resulting joined table.
        # We then have to also watch out for people who apparently exist
        # but have been deleted (it makes sense to catch them as "non-people",
        # else it'd be easy not to notice their jobs as needing dealing with)
        
/*      NOTE:
        MJ, 2009-01-20:
        I have modified these queries to accommodate the changes to $join_string required
        to support formula definitions.
        In many cases these changes have simplified the queries by replacing strings like 'jobs_'.$yearval.'.*' with 'A.*',
        and so on.
        $join_string is defined in build_jobs_select.php.
*/

        $full_query = 'SELECT A.* FROM jobs_'.$yearval.' AS A '.$join_string.' WHERE ((C.uname IS NULL) || (C.deleted = TRUE)) && (NOT A.uname IS NULL) && (A.deleted = FALSE) ORDER BY year, prgroup, paper, name';

	$querydesc = "jobs allocated to people not in database";

   } elseif ($filter_special == "filter_nonallocated") {

        // NB, <=> is the null-safe comparison operator, which returns 1 rather than
        // NULL if both sides are NULL

        $full_query = 'SELECT A.* FROM jobs_'.$yearval.' AS A '.$join_string.' WHERE (A.uname <=> NULL) && (A.deleted = FALSE) ORDER BY year, prgroup, paper, name';

	$querydesc = "all jobs not allocated to someone";

   } elseif ($filter_special == "filter_deleted") {

        if (!$isadminuser) {
           $filter_special = 'nofilter_special';
        } else {
           $full_query = 'SELECT A.* FROM jobs_'.$yearval.' AS A '.$join_string.' WHERE (A.deleted = TRUE) ORDER BY year, prgroup, paper, name';
           $querydesc = "all jobs marked deleted";
           $deletedjobmode = TRUE;
        }

   } elseif (!strncmp($filter_special, "outdiv", 6)) {

        //$division = $filter_special[6];
        $division = substr($filter_special, 6);

        $full_query = 'SELECT A.* FROM jobs_'.$yearval.' AS A '.$join_string.
                      ' WHERE (NOT C.uname IS NULL) && instr(concat(trim(upper(division)), ","),  concat(trim(upper("'.$division.'")), ",")) '.
                      ' && (NOT instr(concat(trim(upper(prgroup)), ","),  concat(trim(upper("'.$division.'")), ","))) && (A.deleted = FALSE) && '.
                      ' (C.deleted = FALSE) ORDER BY uname, year, prgroup, paper, name';


	$querydesc = "all jobs done by people in division $division that are not in subject group $division "; 
        $dopointsummary = TRUE;
        $dopastyearsummary = FALSE;

   } elseif (!strncmp($filter_special, "alldiv", 6)) {

        //$division = $filter_special[6];
        $division = substr($filter_special, 6);

        /*$full_query = 'SELECT A.* FROM jobs_'. $yearval.' AS A '.$join_string.
                      ' WHERE (NOT C.uname IS NULL) && (division = \''.$division.'\') '.
                      ' && (A.deleted = FALSE) && (C.deleted = FALSE) ORDER BY uname, paper, name';*/
        $full_query = 'SELECT A.* FROM jobs_'. $yearval.' AS A '.$join_string.
                      ' WHERE (NOT C.uname IS NULL) && instr(concat(trim(upper(division)), ","),  concat(trim(upper("'.$division.'")), ",")) '.
                      ' && (A.deleted = FALSE) && (C.deleted = FALSE) ORDER BY uname, paper, name';


        $querydesc = "all jobs done by people in division $division";
        $dopointsummary = TRUE;
        $dopastyearsummary = TRUE;

   }  
?>
