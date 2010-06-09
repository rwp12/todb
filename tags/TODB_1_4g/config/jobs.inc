<?php 

// $jobitems is a list of all of the columns in the sql jobs table, in
// the order that we extract them
//

$jobitems = array ("id", "course", "year", "paper", "prgroup", "name", "type", "hours", "term", "uname", "points", "note", "venue","timeslots","updatetime");

// $adminjobcols is a list of all the columns that should be shown to an
// administrative user in edit mode
$adminjobcols = array ("course", "year", "paper", "prgroup", "name", "type", "hours", "term", "uname", "note", "venue", "timeslots","updatetime", "points");

// $adminjobcolshdr are the strings for the header line in edit mode
$adminjobcolshdr = array ("Course", "Year", "Paper", "Group", "Name", "Type", "Hours", "Term", "Person", "Notes", "Venue", "Timeslots","Last Updated", "Pts");

// $updateableadminjobcols is what it says on the tin...
// MJ 20090316: added to JBS config
$updateableadminjobcols = array ("course", "year", "paper", "prgroup", "name", "type", "hours", "term", "uname", "venue", "timeslots", "note", "points");

// the corresponding column titles
$updateableadminjobcolshdr = array ("Course", "Year", "Paper", "Subj. Grp", "Name of Job", "Job Type", "Hours", "Term", "Person", "Venue", "Timeslots", "Notes", "Pts");

// $adminjobcolwidths are the widths for the fields in the job editing form
$adminjobcolwidths = array (5, 2, 4, 3, 40, 3, 3, 3, 15, 15, 15, 20, 5);

// the order in which jobs will be displayed in the jobs screen
$jobordering = "year, paper, name";

// $jobcols is a list of all the columns that should be shown to an
// ordinary user

// JPMG added notes column to public view, 08th oct 07
$jobcols = array ("course", "paper", "prgroup", "name", "type", "hours", "term", "uname", "points"/*, "note"*/);

// $jobcolshdr are the strings for the header line in non-edit mode
$jobcolshdr = array ("Course", "Paper", "Group", "Name", "Type", "Hours", "Term", "Person", "Pts"/*, "Notes"*/);

?>
