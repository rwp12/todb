<?php
header("HTTP/1.1 303 See Other");
// redirects are required to be fully qualified URIs
$operation = $_POST['GenericOperation'] ;
$year = $_POST['GenericYear'];
$redirect = "http://".$_SERVER['HTTP_HOST'].dirname($_SERVER['PHP_SELF']).'/'.
trim($operation)."?yearval=".$year;
//error_log("Operation is #$operation#, dirname(operation) is #".dirname($operation)."# and year is #$year# ");
error_log("Redirecting to $redirect");
header("Location: $redirect");
?>

