<?php

require('config/config.inc');
require('config/db.inc');
require('useful.inc');
require('config/years.inc');
require('auth.inc');
//require('config/jobs.inc');
//require('config/people.inc');
require('locks.inc');
require('common1.inc');
// start the output process here
require('config/header.inc');
require('config/top.inc');


// we set $tablename to be the table to be edited before common1.inc ,
// but here it's the name of the script to be submitted to ...
$tablename = 'timetable';
require('common2.inc');


// ::::::::::::::::::::::::::::::::::
// include the necessary timetable smarts here:
require('timetable_functions.inc');

// show the title:
$info = "<h1>Time table view facility</h1>";
echo $info;


// Display the timetable here:
ShowTimetable(null);


// finish off with the footer
require('config/footer.inc');

?>

