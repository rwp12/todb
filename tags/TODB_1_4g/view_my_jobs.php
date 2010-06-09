<?php

   require('config/config.inc');
   require('useful.inc');

   if (get_exists('yearval')) {
      $yearval = "yearval=".$_GET['yearval']."&";
   } else {
      $yearval = "";
   }

   header("HTTP/1.1 303 See Other");
   // redirects are required to be fully qualified URIs
   $redirect = "http://".$_SERVER['HTTP_HOST'].
        dirname($_SERVER['PHP_SELF'])."/view_people.php?".$yearval.
        "view_my_jobs"; 
   header("Location: $redirect");

   exit;

?>
