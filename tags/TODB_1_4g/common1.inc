<?php

// We do much the same thing in both view_people.php and view_jobs.php
// Just have to make sure that $tablename and $tablething are pre-set!
// ($tablename is the name of the table, and $tablething is the name
// of a single instance of a thing in the table, used when constructing
// add/edit buttons)
// We also require that the jobs.inc/people.inc/whatever.inc file defines
// an $adminjobcols/$adminpersoncols/$adminwhatevercols array

// first see if we've hit the home button, and do a redirect

if (post_match('return', 'Home')) {
   header("HTTP/1.1 303 See Other");
   // redirects are required to be fully qualified URIs
   $redirect = "http://".$_SERVER['HTTP_HOST'].
	dirname($_SERVER['PHP_SELF'])."/index.php"; 
   header("Location: $redirect");

   exit;
}

// next we generate a unique window id, if we haven't already got one

if (post_exists('windowid')) {
   $windowid = $_POST['windowid'];
} else {
   $windowid = uniqid("view_".$tablename);
}

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
        break;
     }
   } 
}
// if we haven't managed to correctly set the yearval from _POST, then:
if ($yeartext == "") {
   $yearval = $current_year;
   foreach (array_keys($valid_years) as $key) {
     if ($yearval == $valid_years[$key][0]) {
        $yeartext = $valid_years[$key][1];
        break;
     }
   } 
}
if ($yeartext == "") {
   die("panic:  cannot set year");
}

// check for $fluxusers 

foreach (array_keys($valid_years) as $key) {
  if ($yearval == $valid_years[$key][0]) {

     // if the latest year is in flux, and the user is not in
     // the list of people allowed to do look at it, redirect them
     // to an explanation page

     if (($valid_years[$key][2] == 0) && ! $isfluxuser) {
        header("HTTP/1.1 303 See Other");
        // redirects are required to be fully qualified URIs
        $redirect = "http://".$_SERVER['HTTP_HOST'].
           dirname($_SERVER['PHP_SELF'])."/notyet.php?yearval=$yearval"; 
           header("Location: $redirect");
           exit;
     }
  }
}

// MJ, 20090603: to support Heads of Classes editing demo-only records
global $is_demo_user;

// check for demo users.  If this is anything other than 'view_jobs',
// reject:
//echo "<p>String position is ".strpos($_SERVER['SCRIPT_NAME'], 'view_jobs')."</p>";
if (($is_demo_user) && !(strpos($_SERVER['SCRIPT_NAME'], 'view_jobs')))
{
    header("HTTP/1.1 303 See Other");
    // redirects are required to be fully qualified URIs
    $redirect = "http://".$_SERVER['HTTP_HOST'].
             dirname($_SERVER['PHP_SELF'])."/notauthd.php?yearval=$yearval";
    header("Location: $redirect");
    exit;
}


//echo "<P>is demo user is $is_demo_user</p.";

// occasionally, we'll want to generate informative messages from our
// php code, before we get to emit any html.  We stick them here:
$GLOBALS['inform_message'] = "";

// ADMIN ONLY SECTION !   [ END MARKER IS "END ADMIN" ]

if ($isadminuser || $is_demo_user) {

   // We check two things - first, whether the edit button got pressed,
   // in which case that's definitively how we want to set things.
   // But if it wasn't pressed, then we look to see if we had an existing
   // state of editing or not-editing, and use that to set our editing
   // state this time round
   //
   // NB the logic of this is very specifically _not_ that we do a
   // post_match('editmode', 'Edit') and then a 
   // post_match('editmodestate', 'Edit') if that fails.  If
   // editmode is set to "Finish Edit", then that over-rides any
   // value in editmodestate ...

   if (post_exists('editmode')) {
      if ($_POST['editmode'] == 'Edit') {
         $adminwantstoedit = TRUE;
      }
   } elseif (post_exists('editmodestate')) {
      if ($_POST['editmodestate'] == 'Edit') {
         $adminwantstoedit = TRUE;
      }
   }


   // But if we now think we want to edit, we always need to check
   // that we've got the lock; if we haven't and no-one else has, we need
   // to acquire it .  But more than that, we want to tag the window
   // we're doing the edits from.

   // if we are prevented from editing, then we're always given the 
   // option of clearing the lock.  This variable toggles this.

   $forceunlockbutton = FALSE;

   // first we see if they've just clicked on the edit button:

   if (post_match('editmode', 'Edit') && 
       post_nomatch('editmodestate', 'Edit')) {

      // if so, we try to get a lock.  NB, we haven't opened the database for
      // reading yet, so we don't need to worry about multiple mysql 
      // connections

      open_db_write();
      if (!getlock($tablename, $thisuser, $windowid)) {
         $adminwantstoedit = FALSE;
         $forceunlockbutton = TRUE;
      } 
      close_db_write();
   }

   // on the other hand, if they've just clicked on the "Finish Edit" button,
   // we should release the lock!

   if (post_match('editmode', "Finish Edit") && 
       post_nomatch('editmodestate', "Finish Edit")) {

      open_db_write();

      if (!releaselock($tablename, $thisuser, $windowid, FALSE)) {
         $GLOBALS['inform_message'] .= "<p>failed to release lock on '".
                             $tablename."' table</p>";
      }
      close_db_write();
   }

   // and if they've just clicked on the forceunlock

   if (post_match('forceunlock', "Force Unlock")) {

      open_db_write();
      releaselock($tablename, $thisuser, $windowid, TRUE);  // FORCE!
      close_db_write();
   }

} 

// END ADMIN

// now would be a good time to open the database for reading.  We assume
// it stays open for the rest of the script

open_db_read();

if ($adminwantstoedit) {
   if (!checklock($tablename, $thisuser, $windowid)) {
      $adminwantstoedit = FALSE;
   }     
} 

// Next see if we've been called as a result of an edit or addition
// of a job/people/whatever line.  

if (post_ncmp($tablething.'Add', 'Apply', 5) || 
    post_match($tablething.'Edit', 'Apply')) 
{

   if (! ($isadminuser || $is_demo_user)) {
      error_log("SECURITY: attempt to insert edit into view_".$tablename.".php by non-admin user \"".$_SERVER['REMOTE_USER']."\" from host \"".$_SERVER['REMOTE_ADDR']."\".");
   } elseif (! $adminwantstoedit) {
      $GLOBALS['inform_message'] .= "<p>You have attempted to post a ".strtolower($tablething)." edit/addition when not in edit mode; this has been rejected.  Did you click 'Finish edit' before 'apply'?<p>";
   } else {

      open_db_write();

      // see comment at top of this file.  We expect there to be an
      // array defined called $adminjobcols or $adminpersoncols or
      // whatever.  We then refer to $$admincolname to get at it ...
      
      $admincolname = "updateableadmin".strtolower($tablething)."cols";

      // pick apart the submitted data.

      if ((post_exists('idlist')) && (!($_POST['duplicate'] == 'duplicate'))) {  // the && clause is to make the 'duplicate' button add instead of updating
         foreach (explode('+',  $_POST['idlist']) as $id) {
            $foundsomething = 0; 
            foreach ($$admincolname as $item) {
                $$item = $_POST[$item."_".$id];
		        if ($item == 'points')
                {
                   $$item *= 100;
                }
                if ($$item != '') {
                   $foundsomething = 1;
                }
                
                // support for demonstration admin users: new value for 'TYPE' column needs to be 'D'
                if (($is_demo_user) && (strtoupper($item) == 'TYPE'))
                {
                    if (strtoupper($$item) != 'D')
                    {
                        $$item = 'D';
                        $GLOBALS['inform_message'] = 'Demonstration admin users can only enter jobs of type "D" for demonstrating.  '.
                                                 'Your submission has been modified such that job type is now "D".';
                    }
                }
                
                // support for demonstration admin users: check that ids of records being edited
                // are all of type 'D' as well:
                // we are in a loop here, with one iteration for each ID in the list.  So, just check the
                // one here:
                if ($is_demo_user)
                {
                    $demo_q = 'select type from jobs_'.$yearval.' where id = '.$id.';';
                    $demo_res = mysql_query($demo_q, $dbread);
                    $demo_row = mysql_fetch_assoc($demo_res);
                    // if the type of the stored record is not D, block the update:
                    if ($demo_row['type'] != 'D')
                    {
                        $GLOBALS['inform_message'] = 'Demonstration admin users can only modify jobs of type "D" for demonstrating [Job ID '.$id.']. '.
                                                     ' (this job type is '.$demo_row['type'].')';
                        // block update by setting 'foundsomething' to 0
                        $foundsomething = 0;
                    }
                    mysql_free_result($demo_res);
                }
            }
            if ($foundsomething != 0) {
               $query = "UPDATE ".$tablename."_".$yearval." SET ";
               $separator = '';
               foreach ($$admincolname as $item) {
                  if (strncmp($item, "ignore", 6) != 0) {
                     $query .= $separator.$item."=".qfix($$item);
                     $separator = ', ';
                  } 
                }
               if (post_exists('deleted_'.$id) && 
                   ($_POST['deleted_'.$id] == 'Delete line')) {
                  $query .= $separator."deleted=1";
               }
               // deal with the possibility of unsetting a delete flag
               if (post_match('filter_special','filter_deleted')) {
                  if (!post_exists('deleted_'.$id)) {
                     $query .= $separator."deleted=NULL";
                  }
               } 
               $query .= " WHERE id='".$id."' LIMIT 1";
               if (($result = mysql_query($query, $dbwrite)) === FALSE) {
                   $GLOBALS['inform_message'] .= "<p>Update failed, id=$id: ";
                   $GLOBALS['inform_message'] .= mysql_error()."</p>";
               }
               
               // if it is the current people table that is being edited,
               // generate a fresh list of users for apache to allow
               // Raven access for:
               // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
               if (($tablename) == 'people' and ($yearval == $current_year))
               {
                    CreateUserList();
               }
               // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
            }
         }
         
      } else { // it'll be a new job/person/whatever being added

         $foundsomething = 0;
         foreach ($$admincolname as $item) {
         	$item2 = $item;
         	if (post_exists('idlist'))
         		{
         			$item2 = $item.'_'.$_POST['idlist']; // $item2 is for when we use the update job popup, but we actually want to add a new job because the duplicate button has been pressed
				}
            $$item = $_POST[$item2];
            if ($item == 'points') {
               $$item *= 100;
            }
            if ($$item != '') {
               $foundsomething = 1;
            }
            
            // support for demonstration admin user entry:
            if (($is_demo_user) && (strtoupper($item) == 'TYPE'))
            {
                if (strtoupper($$item) != 'D')
                {
                    $$item = 'D';
                    $GLOBALS['inform_message'] = 'Demonstration admin users can only enter jobs of type "D" for demonstrating.  '.
                                                 'Your submission has been modified such that job type is now "D".';
                }
            }
         }
         if ($foundsomething != 0) {
            $query = "INSERT INTO ".$tablename."_".$yearval." SET ";
            $separator = '';
            foreach ($$admincolname as $item) {
            	
               // AEC will allow Null entry to quota in which case it takes DB default value	
               if (($item == 'quota') && ($$item == NULL) ) {
                   continue;
               }
               
               if (strncmp($item, "ignore", 6) != 0) {
                  $query .= $separator.$item."=".qfix($$item);
                  $separator = ', ';
               }
            }

            //echo "<p>Insert query is <br />$query</p>";

            if (($result = mysql_query($query, $dbwrite)) === FALSE) {
                $GLOBALS['inform_message'] .= "<p>Update failed, id=$id: ";
                $GLOBALS['inform_message'] .= mysql_error()."</p>";
            }
            else
            {
               // if it is the current people table that is being edited,
               // generate a fresh list of users for apache to allow
               // Raven access for:
               // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
               if (($tablename) == 'people' and ($yearval == $current_year))
               {
                    CreateUserList();
               }
               // :::::::::::::::::::::::::::::::::::::::::::::::::::::::::::

            } 
         }
      }

      close_db_write();
   }
}

// and check if we're doing a purge-deleted.  To keep things simple
// and safe, we make sure we've been asked to purge the same table
// as we're looking at now.

if (post_match('PurgeDeleted', 'PurgeDeleted'.ucfirst($tablename))) 
{

   if (! ($isadminuser) || ($is_demo_user)) {
      error_log("SECURITY: attempt to insert edit into view_".$tablename.".php by non-admin user \"".$_SERVER['REMOTE_USER']."\" from host \"".$_SERVER['REMOTE_ADDR']."\".");
   } elseif (! $adminwantstoedit) {
      $GLOBALS['inform_message'] .= "<p>Bogus attempt to post ".$tablething." edit/addition when not in edit mode;  rejected<p>";
   } else {

      open_db_write();

      $query = "DELETE FROM ".$tablename."_".$yearval.
               " WHERE (deleted = TRUE)";
      if (($result = mysql_query($query, $dbwrite)) === FALSE) {
          $GLOBALS['inform_message'] .= "<p>Purge failed, id=$id: ";
          $GLOBALS['inform_message'] .= mysql_error()."</p>";
      } 
  
      close_db_write();
   }
} else if (post_exists('PurgeDeleted')) {
  $GLOBALS['inform_message'] .= "<p>PurgeDeleted set, but value is \"".$_POST['PurgeDeleted']."\", not \"Purge Deleted ".ucfirst($tablething)."\"</p>";
}


// Create a list of users for Raven/Apache to authenticate with
// ::::::::::::::::::::::::::::::::::::::::::::::::::::::::::::
// Inputs: 
// 			$dbread - database read connection
//			$raven_user_filename - name of users filename that Apache is configured to read
//          $current_year - the current academic year (used to determine which year's users will have read access)
//          $allowedusers - admin users array
//          $fluxusers - flux users array
function CreateUserList()
{
    global $dbread, $current_year, $raven_user_filename, $allowedusers; 
    global $fluxusers;  //AEC
	
    // filename
    // read from globacl configuration or set her if not
    // already set:
	if (!isset($raven_user_filename))
	{
        $raven_user_filename = 'TODB_Raven_Users.txt';
	}

    // MySQL query to get a list of CRSIDs:
    $crsid_query = "select crsid from people_$current_year where deleted is false;";
    //echo "<p>$crsid_query</p>";
    $crsid_result = mysql_query($crsid_query, $dbread);

    // String to hold space-separated list of CRSIDs
    $raven_users = 'TODB_users: ';
    // Iterate through result set populating list:
    while ($crsid_row = mysql_fetch_array($crsid_result))
    {
        $raven_users .= $crsid_row[0];
        // the following line removes long lines of spaces that might
        // appear if lots of CRSID columns are null/empty
        $raven_users = trim($raven_users).' ';
    }
    // free result set
    mysql_free_result($crsid_result);

    // now append the lists of flux and admin users:
    $raven_users .= implode(' ', $allowedusers).' '.implode(' ', $fluxusers);

    // echo to screen (for now):
    //echo "<P>$raven_users</p>";

    // write to file
    $crsid_out = fopen($raven_user_filename, 'wb');
    fwrite($crsid_out, $raven_users, strlen($raven_users));
    fclose($crsid_out);
    // Done!
}


 
?>
