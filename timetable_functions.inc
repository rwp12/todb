<?php

// ShowPeopleTimetable
// -------------------
// This function displays a timetable with jobs by a subset of people only.
// ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// use this function with a mysql resultset as a parameter.
// It assumes that the uname column represents person unique names
// It then constructs a list of names and passes this to the ShowTimetable
// function.
function ShowPeopleTimetable($result)
{
    // if a mysql resultset was provided
    $personlist = array();
    if (isset($result))
    {
        mysql_data_seek($result, 0);
        while ($row = mysql_fetch_assoc($result))
        {
            array_push($personlist, "'".$row['uname']."'");
        }
    }
    $person_param = implode(', ', $personlist);
    if (strlen(trim($person_param))>0) ShowTimetable($person_param);

}

function ShowTimetable($person)
{
    global $dbread, $yearval, $tt_start_times, $tt_yearlist, $tt_days_of_week;
    // MAKE A TIME TABLE from JOBS data
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~
    // Matthew Jones, August 2009
    // ~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~~

    // Define the storage array dimensions:
    // ::::::::::::::::::::::::::::::::::::::::::::::
    // $ttvalues represents every time table slot
    // over a number of years, weeks, etc
    $ttvalues = array();

    // a number of arrays are used to construct this array;
    //  - - - - - - - - - - -- - - - - - - - - - - - - - -
    // List of years
    $yearlist = array('4','5');
    if (isset($tt_yearlist)) $yearlist = $tt_yearlist;

    // These need to match the start times in the timeslots field in the jobs table
    // any start times not in this list will be omitted from the timetable.  It is the
    // lesser of two weevils, I promise!   (because of issues with ordering, mainly)
    $start_times = array('9', '10', '11', '11.3', '12', '2', '3', '4');
    // overwrite from config file:
    if (isset($tt_start_times)) $start_times = $tt_start_times;

    // days of week on which lectures are given
    $days_of_week = array('M', 'Tu', 'W', 'Th', 'F');
    // overwrite from config file:
    if (isset($tt_days_of_week)) $days_of_week = $tt_days_of_week;
    
    // terms in which the jobs are given
    $termlist = array('M', 'L', 'E');

    // number of weeks in each term
    //$week_nums = array('1','2','3','4','5','6','7','8','9','10','11','12');

    $uname_where = '';
    if (isset($person))
    {
        //echo "<p>Person is set - $person</p>\n";
        if (strlen($person) > 0) $uname_where = " uname in ($person) and ";
    }
    

    // DATABASE QUERY to get the list of jobs, who did them and when:
    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
    $tt_query = "select id, name, uname, timeslots, year, term, venue from jobs_$yearval where $uname_where deleted is FALSE and length(trim(timeslots))>0 order by name;";
    //echo "<P>Query is: $tt_query</P>";
    open_db_read();
    $tt_result = mysql_query($tt_query, $dbread);
    while ($row = mysql_fetch_array($tt_result))
    {
        $person = $row[2];
        $jobname = $row[1];
        $timeslots = $row[3];
        $db_year_list = $row[4];
        $db_term_list = $row[5];
        $venue = $row[6];
        
        // break year list into years/parts:
        $db_year_arr = explode(',', $db_year_list);
        
        // break term list into single terms:
        $db_term_arr = explode(',', $db_term_list);
        
        // run through each of the years in the list, one by one:
        foreach($db_year_arr as $db_year)
        {
            // remove any whitespace that might be confusing:
            $db_year = trim($db_year);
            //run through each of the terms in the list, one by one:
            foreach ($db_term_arr as $db_term)
            {
                $db_term = trim($db_term);

        //$timeslots = SplitTimeslotString($timeslots);
        //echo "# $timeslots : processed - ".SplitTimeslotString($timeslots)." <br />\n";
        $timeslots = SplitTimeslotString($timeslots);


        // break apart the timeslots string and assign to the big array above:
        // - - - - - - - - - - -- - - - - - - - - - - - - - - - - - - - -
        $lect_times = explode(',', $timeslots);
        // --> yields e.g. 'Wk5: Tu.4'
        foreach ($lect_times as $ltime)
        {
            // remove any ':' chars:
            $ltime = str_replace(':', '', $ltime);

            // split into week value and day/time value
            list($weekval, $dayval) = explode(' ', trim($ltime));

            // week number:
            $weeknumber = substr(trim($weekval), 2);

            // day of week and time of day:
            //list($dow, $tod) = explode('.',$dayval);
            $dow = trim(substr($dayval, 0, strpos($dayval, '.')));
            $tod = trim(substr($dayval, strpos($dayval, '.')+1));
            //echo "Day of week is <b>$dow</b> and time of day is <b>$tod</b><br />\n";

            // now assign to array:
            $person_val = '';
            if (strlen(trim($person)) > 0) $person_val = "<br /><i>($person)</i>";
            // VERSION 1 - working!
            // $ttvalues[$db_year][$db_term][$weeknumber][$dow][$tod] .= $jobname.$person_val;

            // if all the required inputs are present, add to array:
            if (isset($db_year) &&
                isset($db_term) &&
                isset($dow) &&
                isset($tod) &&
                isset($weeknumber) &&
                (isset($jobname) || isset($person_val))
                )
            {
                // append...
                if (isset($ttvalues[$db_year][$db_term][$dow][$tod][$weeknumber]))
                            $ttvalues[$db_year][$db_term][$dow][$tod][$weeknumber] .= $jobname.$person_val.'<br />'.$venue;
                // or add:
                        else $ttvalues[$db_year][$db_term][$dow][$tod][$weeknumber] = $jobname.$person_val.'<br />'.$venue;
            }
        }

            } // end of year loop
        } // end of term loop
    }

    mysql_free_result($tt_result);

    // DISPLAY ARRAY as TIME TABLE
    // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

    // sort the array so that the timetable goes in the right order (and this is
    // difficult to do via the SQL statement, because the timeslot info is stored
    // in a free text field)
    // it would be nice to sort each of the array dimensions recursively, it turns out
    // that none of the values follow numerical or alphabetic sorting patterns...
    // (except years and weeks):
    ksort($ttvalues);


    // get a list of week numbers (week 1, week 2, etc) actually in use:
    $weeks_in_use = array();
    // data_present is an array of boolean.  When the tables are displayed, the system checks this
    // first to see if any items are timetabled for this year and term.  If not, no table is displayed.
    $data_present = array();
    foreach (array_keys($ttvalues) as $year)
    {
        foreach ($termlist as $term)
        {
            foreach ($days_of_week as $weekday)
            {
                foreach ($start_times as $stime)
                {   //AEC add isset check to stop warning
                	if (isset($ttvalues[$year][$term][$weekday][$stime])) {
                		                	
                    $possible_weeks = array_keys($ttvalues[$year][$term][$weekday][$stime]);
                    foreach ($possible_weeks as $pw)
                    {
                        if (!isset($weeks_in_use[$year][$term][$pw]))
                        {
                            // indicate that week $pw is in use, so this will be displayed as a column later on.
                            $weeks_in_use[$year][$term][$pw] = true;
                            // indicate that data is present for this year and term
                            $data_present[$year][$term] = true;
                        }
                    }
                }
            }
        }
    }
    }



    foreach (array_keys($ttvalues) as $year)
    {
        foreach ($termlist as $term)
        {
            $show_heading = true;
            
            if ($data_present[$year][$term])
            {
                echo "<h2>Year: $year; Term: $term</h2>";
                echo "<table class='unit_table'>";
                foreach ($days_of_week as $weekday)
                {
                    foreach ($start_times as $stime)
                    {
                        ksort($weeks_in_use[$year][$term]);
                        // display headings
                        if ($show_heading)
                        {
                            echo "<thead><tr><th>Day</th><th>Time of day</th>";
                            foreach (array_keys($weeks_in_use[$year][$term]) as $week)
                            {
                                echo "<th>Week $week</th>";
                            }
                            echo "</tr></thead>\n<tbody>";
                            $show_heading = false;
                        }

                        echo "<tr><td><b>$weekday</b></td><td>".sprintf('%01.2F',$stime)."</td>";

                        foreach (array_keys($weeks_in_use[$year][$term]) as $week)
                        {
                            // display the job data
                            if (isset($ttvalues[$year][$term][$weekday][$stime][$week]))
                               echo "<td>".$ttvalues[$year][$term][$weekday][$stime][$week].'</td>';
                            else echo "<td class='shaded'></td>";
                        }
                        echo "</tr>\n";
                    }
                }
                echo "</tbody></table>";
            }
        }
    }
}
// end of ShowTimetable function


    // -----------------------------------------------------------------------
    // The idea with this function is to hand it the complete timetable field
    // string from the timeslots field of the data.
    // Then this function does its best to convert this string into the
    // standard format understood by view_timetable.php
    // -----------------------------------------------------------------------
    // MJ, August 2009.
    // With apologies to anyone who tries to understand it...
    // -----------------------------------------------------------------------
    // TODO (bright ideas!)
    // 1.1 support for week ranges, e.g. Wk1-5: Tu.3
    // 1.2 ???
    // 2. support for starting dates (from), e.g. Tu.2, Th.3 starting week 5
    // 3. as above, but using dates: Tu.2.30 starting 28 October
    // -----------------------------------------------------------------------
    function SplitTimeslotString($input)
    {
        // identify weeks:
        // :::::::::::::::
        $week_tokens = array();
        preg_match_all("(WE*K\s*[0-9]+)", strtoupper($input), $week_tokens);
        //print_r($week_tokens);

        // split the string apart based on delimeters identified as weeks
        // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        $day_tokens = array();
        $day_tokens = preg_split("(WE*K\s*[0-9]+)", strtoupper($input));
        //print_r($day_tokens);

        // Correct the format of the weeks - e.g. Week 4 --> Wk4
        // :::::::::::::::::::::::::::::::::::::::::::::::::::::
        // fix up the weeks:
        $fixed_weeks = array();
        foreach ($week_tokens[0] as $unfixed_week)
        {
            $weeknums = array();
            // find the number:
            eregi("[0-9]+", $unfixed_week, $weeknums);
            
            //AEC eregi is deprecated - needs to be replaced but time testing not justified as not sure if timetabling will be used
            //preg_match("/[0-9]+/i", $unfixed_week, $weeknums);
            
            foreach ($weeknums as $wknum)
            {
                $fixed_weeks[] = "Wk$wknum";
            }
        }


        // init the return string
        $return = '';

        // For each of the things delimeted by weeks, process.
        // These are supposed to be single timeslots, but our
        // (wonderful and normally careful) users cannot be
        // trusted not to write Wk7: Tu.4, Th.3 rather than Wk7: Tu.4, Wk7 Th.3
        // as they should.
        // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        // Fix up the timeslots:
        foreach ($day_tokens as $dkey => $dt)
        {
            // check for repeat time slots, e.g. Wk3: Tu3, W.5; ...
            $timeslot_tokens = array();
            preg_match_all("([A-Z]+[\s\.]*[0-9\.\:]+)",$dt, $timeslot_tokens);
            foreach ($timeslot_tokens[0] as $timeslot)
            {
                $return .= $fixed_weeks[$dkey-1].": ".ReFormatTimeslot($timeslot).", ";
            }
        }

        return substr($return, 0, strlen($return)-2);

    }


    // -----------------------------------------------------------------------
    // this function uses regexes to reformat a potentially
    // badly-formatted timeslot string into something the
    // timetable-generating software can reliably process.
    // AEC 24 03 10 Added Saturday to this array - needs to be switched on in 
    // $days_of_week before Sat entries will appear in the display
    // -----------------------------------------------------------------------
    function ReFormatTimeslot($input)
    {
        // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        // day map lists
        $day_map = array(
                    'M' => array('M', 'MON', 'MDY', 'MDAY', 'MONDAY', 'MODAY'),
                    'Tu' => array('T', 'TU', 'TUE', 'TUES', 'TUESDAY'),
                    'W' => array('W', 'WED', 'WDN', 'WE', 'WEDS', 'WEDNESDAY', 'WDY'),
                    'Th' => array('TH', 'THS', 'THURS', 'THDAY', 'THURSDAY', 'THRUSDAY'),
                    'F' => array('F', 'FRI', 'FDAY', 'FRIDAY'),
        			'S' => array('S', 'SAT', 'SDAY', 'SATURDAY'),
        );

        // complete day match string:
        $day_match_str = '';
        foreach ($day_map as $dm)
        {
            $prefix = '';
            if (strlen($day_match_str) > 0) $prefix = '|';
            $day_match_str .= $prefix.implode('|', $dm);
        }

        // ::::::::::::::::::::::::::::::::::::::::::::::::::::::::
        $cc = array();
        // get the day of the week:
        $day_match = '';
        eregi("($day_match_str)", strtoupper($input), $cc);
        if (isset($cc[0])) {
            $day_match =  $cc[0];
        }

        // get number of hours
        $cc = null;
        $cc = array();
        $numhours = '';
        eregi("([0-9]+[\.:]*[0-9]*)", strtoupper($input), $cc);
        if (isset($cc[0])) {
            // echo the paper:
            $numhours =  $cc[0];
        }

        // look up the day match:
        $matched = 0;
        foreach ($day_map as $dayk => $dayval)
        {
            foreach ($dayval as $day)
            {
                if ($day == $day_match) {
                    $matched = $dayk;
                    break;
                }
            }
            if ($matched) break;
        }

        // replace any ':' with '.'
        // (people are writing times so eleven thirty could be written as:
        // 11.30 (correct) or 11:30 ('incorrect')
        $numhours = str_replace(':', '.',$numhours);

        // check for empty variables
        if (($numhours) && ($matched))
        {
            // implicitly convert to numbers (so to convert 11.30 to 11.3)
            $numhrs_numeric = $numhours + 0.0;
            // and return:
            return $matched.'.'.$numhrs_numeric;
        }
        else return '';

    }






?>
