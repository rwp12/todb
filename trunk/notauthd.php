<?php

require('config/config.inc');
require('config/header.inc');
require('config/top.inc');
require('useful.inc');
require('config/db.inc');
require('config/years.inc');

// we don't want to call common1.inc or we'll get an infinite loop of
// redirects.  Instead, we mostly duplicate the yearval code here.

// work out what year we're working with

$yeartext = "";
$yearval = "";
if (post_exists('yearval')) {
   $yearval = $_POST['yearval'];
}
if (get_exists('yearval')) {
   $yearval = $_GET['yearval'];
}
if ($yearval != "") {
   foreach (array_keys($valid_years) as $key) {
     if ($yearval == $valid_years[$key][0]) {

        $yeartext = $valid_years[$key][1];

     }
   }
} 

?>
<p>
As a demonstration admin user, you are only authorised to view Jobs.<br />
Please click here to view jobs: <a href="view_jobs.php?yearval=<?php echo $yearval; ?>">View Jobs</a> <br />
...or go to the home page: <a href="index.php">Home</a>.
<p>
<?php
require('config/footer.inc');
?>
