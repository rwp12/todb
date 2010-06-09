<?php 

// $personitems is a list of all of the columns in the sql people table, in
// the order that we extract them
// Any variables to be displayed in any of other variables MUST be in this list too, otherwise various things break.
$personitems = array ("id", "uname", "crsid", "engid", "division", "title", "called", "surname", "initials", "room", "phone",
                      "job_title", "college", "OK06", "status", "PostalAddress", 'quota', "mobile", "ice", "homephone", "updatetime");

// :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// $adminpersoncols is a list of all the columns that should be shown to an
// administrative user in edit mode
// Note: these MUST exist in the $personitems array (case-sensitive!)
$adminpersoncols = array ("uname", "crsid", "division", "title", "called",  "initials", "surname", "room", "phone", "homephone",
                          "mobile", "ice", "job_title",  "PostalAddress", "college", "status", "OK06", "updatetime", "quota");

// $adminpersoncolshdr are the strings for the header line in edit mode
$adminpersoncolshdr = array ("Unique Name", "CRSID", "Div", "Title", "Called", "Initials", "Surname", "Room", "Phone", "Home Phone",
                             "Mobile", "In case of<br /> Emergency", "Job Title",  "PostalAddress", "College", "Status", "Duties-OK", "Last Updated", "Quota", "Points");

// updateableadminperson cols is the list to show in the addperson popup
// MJ, 2009-07-08: updated so that this applies to person_popup as well.
// Note: these MUST exist in the $personitems array (case-sensitive!)
$updateableadminpersoncols = array ("uname", "crsid", "division", "title", "called",  "initials", "surname", "room", "phone",
                                    'homephone', 'mobile', 'ice', "job_title", "college", "PostalAddress", "status", "OK06", "quota");

// updateableadminpersoncolshdr is the header list for the addperson popup
$updateableadminpersoncolshdr = array ("Unique Name", "crsid", "Div", "Title", "Called", "Initials", "Surname", "Room", "Phone",
                                       'Home Phone', 'Mobile', 'ICE', "Job Title", "College", "Postal Addr.", "Affl. Status",  "Duties-OK", "Quota");

// $adminpersoncolwidths are the widths for the fields in the person editing form
$adminpersoncolwidths = array (15, 4, 4, 1, 4, 4, 15, 10, 10, 10, 10, 10, 10, 4, 20, 10, 15, 4);

// $personcols is a list of all the columns that should be shown to an
// ordinary user
// Note: these MUST exist in the $personitems array (case-sensitive!)
$personcols = array ("uname", "crsid", "division", "title", "initials", "surname", "room", "phone", "job_title", "college",
                     "status", "OK06");


// $personcolshdr are the strings for the header line in non-edit mode
$personcolshdr = array ("Show Jobs", "CRSID", "Div", "Title", "Initials", "Surname", "Room", "Phone", "Job Title", "College",
                        "Affl. Status","Duties-OK", "Points");

$adminpersoncolspointstotal = $adminpersoncols;
array_push($adminpersoncolspointstotal,"sum");

$personcolspointstotal = $personcols;
array_push($personcolspointstotal,"sum");

?>
