<?php
//session_start();  Done in config inc

        $isadminuser = FALSE;
        $isfluxuser = FALSE;
        // MJ, 20090603: to support Heads of Classes editing demo-only records
        $is_demo_user = FALSE;
        $thisuser = 'NONE';
        $adminwantstoedit = FALSE;

if (array_key_exists("REMOTE_USER", $_SERVER)){
	//$_SERVER['REMOTE_USER']=$_SESSION['REMOTE_USER'];
	$thisuser = $_SERVER['REMOTE_USER'];

}


// MJ, 20090526:
// Authentication does not work properly without the raven authentication module
// installed on the server.
// This necessitates having two versions of auth.inc - a local one for dev and
// a production one for Raven.
// My idea is that if the request comes from localhost (127.0.0.1),
// and the browser's ip is ALSO localhost,
// then perhaps the browser and server are the same machine, and this is probably a dev
// situation; in which case, thisuser should
// be set to the developer's id for dev work:
else
{
    $user_ip = $_SERVER['REMOTE_ADDR'];
    $server_ip = $_SERVER['SERVER_ADDR'];
    //echo "<p>User IP is $user_ip</p>";
    if(($user_ip == $server_ip) && ($user_ip == '127.0.0.1'))
    {
        //echo '<p>Localhost detected</p>';
        //$isadminuser = true;
        $isfluxuser = FALSE;
        //$thisuser = 'mrj35';
        global $default_local_user;
        $thisuser = $default_local_user;
        //$adminwantstoedit = true;
        $_SESSION['REMOTE_USER'] = $thisuser;
        $_SERVER['REMOTE_USER'] = $thisuser;
        //echo '<p>Remote user on server is '.$_SERVER['REMOTE_USER'].'</p>';
    }
}

/*if (array_key_exists('REMOTE_USER', $_SERVER))
   $thisuser = crs2eng($_SERVER['REMOTE_USER']); //once usage of engid to crsid has been implemented this can be removed
*/
$viewasuser = 'FALSE';
if (array_search($thisuser,  $allowedusers) !== FALSE) 
{
   $isadminuser = TRUE;
   // AEC Allow admin user to be able to view as if non-admin user
   if (array_key_exists('viewasuser', $_POST))
   { 
  		$viewasuser = TRUE;
  		$isadminuser = FALSE;
  }
}

if (array_search($thisuser, $fluxusers) !== FALSE)
   $isfluxuser = TRUE;
   
// MJ, 20090603: to support Heads of Classes editing demo-only records
if (array_search($thisuser, $demonstration_admin_users) !== FALSE)
{
   //if (!$isadminuser)
   //{
     $is_demo_user = TRUE;
   //}
}
// MJ# to see who the current user is...
//echo "<!-- this user is # $thisuser # -->\n";

// finer-grained access control:
// :::::::::::::::::::::::::::::
// MJ, 20090604:
/* The idea here is to look up the PHP filename, yearval and crsid to see if a user has access to particular
   page. The .htaccess approach can only either allow or reject a user on a site(directory)-wide basis.
*/
/*
$is_allowed_to_view = false;

// now get the name of the PHP file only:
// ::::::::::::::::::::::::::::::::::::::
$php_filename = $_SERVER['SCRIPT_NAME'];
$pieces = explode('/', $php_filename);
// counting the pieces...
$length = count($pieces);
$php_file_only = $pieces[$length-1];
//echo "<p>Current filename is $php_file_only</p>";

// $yearval is yearvalue
// user is $thisuser
$yv = $_GET['yearval'];
$rights_query = "select R_read from pageauth where yearval = '$yv' and phpfile = '$php_file_only' and username = '$thisuser';";
echo "<p>Query is $rights_query</p>";
$rights_res = mysql_query($rights_query, $dbread);
while ($row = mysql_fetch_array($rights_res))
{
    $R_read = $row[0];
    break;
}
mysql_free_result($rights_res);
if (isset($R_read))
{
    echo '<p>This user has read rights for this page</p>';
}
*/

?>
