<?php 

// $unititems is a list of all of the columns in the sql people table, in
// the order that we extract them
//

$unititems = array ("id", "uname", "course", "ordering", "sgrps", "name", "assessmode", "running", "global", "note", "updatetime");

// $adminunitcols is a list of all the columns that should be shown to an
// administrative user in edit mode

$adminunitcols = array ("uname", "course", "ordering", "sgrps", "othergroups", "name", "assessmode", "running", "global", "note", "updatetime");

$updateableadminunitcols = array ("uname", "course", "ordering", "sgrps", "name", "assessmode", "running", "global", "note");

// $adminunitcolshdr are the strings for the header line in edit mode

$adminunitcolshdr = array ("Paper/Unit", "Course", "Order", "Subj Grps", "Other Groups", "Full Name", "Mode", "Running", "Global Interest", "Note", "Last updated");

$updateableadminunitcolshdr  = array ("Paper/Unit", "Course", "Order", "Subj Grps", "Full Name", "Mode", "Running", "Global Interest", "Note");

// $adminunitcolwidths are the widths for the fields in the unit editing form

$adminunitcolwidths = array (6, 5, 5, 8, 40, 8, 1, 1, 30);

// $unitcols is a list of all the columns that should be shown to an
// ordinary user

$unitcols = array ("uname", "course", "sgrps", "name", "assessmode", "running", "global");

// $unitcolshdr are the strings for the header line in non-edit mode

$unitcolshdr = array ("Paper/Unit", "Course", "Subj Grps", "Full Name", "Mode", "Running", "Global Interest");

?>
