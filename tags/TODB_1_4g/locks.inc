<?php

function getlock($tabname, $user, $windowid) {

  global $inform_message;
  global $dbwrite;
  global $yearval; 

  $query = 'LOCK TABLES editlocks_'.$yearval.' WRITE';
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_message .= '<p>ERROR: lock editlocks_'.$yearval.' table failed: ';
     $inform_message .= mysql_error(). '</p>'; 
     return FALSE;
  } 
  

  $query = 'SELECT * FROM editlocks_'.$yearval.' WHERE dbname = "'.$tabname.'"'; 
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_message .= '<p>ERROR: select('.$tabname.') from editlocks_';
     $inform_message .= $yearval.' failed: ' . mysql_error(). '</p>';
     return FALSE;
  } 

  $line = mysql_fetch_array($result, MYSQL_ASSOC);

  mysql_free_result($result);

  if ($line['inuse']) {
     list ($currentuser, $currentwindowid) = 
        explode(':', $line['currentuser']);
     if ($user == $currentuser) { 
        $inform_message .= "<p>Failed to get edit lock on the '".$tabname;
        $inform_message .= "' database.  It is currently being edited by ";
        $inform_message .= "you (<b>".$currentuser."</b>) in another ";
        $inform_message .= "window (id: <b>";
        $inform_message .= $currentwindowid."</b>)</p>";
     } else {
        $inform_message .= "<p>Failed to get edit lock on the '".$tabname;
        $inform_message .= "' database.  It is currently being edited by ";
        $inform_message .= "<b>".$currentuser."</b></p>";
     }     

     return FALSE;
  }

  $query = 'UPDATE editlocks_'.$yearval.' SET inuse = 1, currentuser = "'.
            $user.':'.$windowid.'" WHERE dbname = "'.$tabname.'" LIMIT 1';
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_mesage .= '<p>ERROR: update editlocks_'.$yearval.'('.$tabname.') ';
     $inform_message .= 'failed: '.mysql_error(). '</p>';
     return FALSE;
  }
  
  $query = 'UNLOCK TABLES';
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_message .= '<p>ERROR: unlock editlock_'.$yearval.' table failed: '; 
     $inform_message .= mysql_error(). '</p>';
     return FALSE;
  } 

  return TRUE;

}

function releaselock($tabname, $user, $windowid, $force) {

  global $inform_message;
  global $dbwrite;
  global $yearval;

  $query = 'LOCK TABLES editlocks_'.$yearval.' WRITE';
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_message .= '<p>ERROR: lock editlock_'.$yearval.' table failed: ';
     $inform_message .= mysql_error(). '</p>'; 
     return FALSE;
  } 

  $doit = TRUE;
  
  if (! $force) {

     $query = 'SELECT * FROM editlocks_'.$yearval.' WHERE dbname = "'.
               $tabname.'"'; 
     if (($result = mysql_query($query, $dbwrite)) === FALSE) {
        $inform_message .= '<p>ERROR: select('.$tabname.') from editlocks_';
        $inform_message .= $yearval.' failed: ' . mysql_error(). '</p>';
        return FALSE;
     }

     $line = mysql_fetch_array($result, MYSQL_ASSOC);

     mysql_free_result($result);

     if (! (($line['inuse'] == 1) && 
            ($line['currentuser'] == $user.":".$windowid))
        ) {
        $doit = FALSE;
     }
  } 

  $success = FALSE;

  if ($doit) { 
  
     $query = 'UPDATE editlocks_'.$yearval.' SET inuse = 0, currentuser = NULL, ';
     $query .= 'lastuser = "'.$user.':'.$windowid.'" WHERE dbname = "';
     $query .= $tabname.'" LIMIT 1';
     if (($result = mysql_query($query, $dbwrite)) === FALSE) {
        $inform_message .= '<p>ERROR: update editlocks_'.$yearval.'(';
        $inform_message .= $tabname.') failed: '.mysql_error(). '</p>';
     } else {
        $success = TRUE;
     }
  }

  $query = 'UNLOCK TABLES';
  if (($result = mysql_query($query, $dbwrite)) === FALSE) {
     $inform_message .= '<p>ERROR: unlock editlock_'.$yearval.' table failed: '; 
     $inform_message .= mysql_error(). '</p>';
     return FALSE;
  } 

  return $success;

}

function checklock($tabname, $user, $windowid, $quiet = FALSE) {

  global $inform_message;
  global $dbread;
  global $yearval;

  $query = 'SELECT * FROM editlocks_'.$yearval.' WHERE dbname = "'.$tabname.'"'; 
  if (($result = mysql_query($query, $dbread)) === FALSE) {
     if (!$quiet) {
        $inform_message .= '<p>ERROR: select('.$tabname.') from editlocks_';
        $inform_message .= $yearval.' failed: ' . mysql_error(). '</p>';
     }
     return FALSE;
  } 

  $line = mysql_fetch_array($result, MYSQL_ASSOC);

  mysql_free_result($result);

  if (($line['inuse'] == 1) && ($line['currentuser'] == $user.":".$windowid)) {
     return TRUE;
  }

  if ($line['inuse'] != 1) {
     if (!$quiet) {
        $inform_message .= "<p>No edit lock appears to exist on the '".$tabname;
        $inform_message .= "' database.</p>";
     }
     return FALSE;
  }

  list ($currentuser, $currentwindowid) = 
        explode(':', $line['currentuser']); 

  if ($user == $currentuser) { 
     if (!$quiet) {
        $inform_message .= "<p>Failed to confirm edit lock on the '".$tabname;
        $inform_message .= "' database.  It is now being edited by ";
        $inform_message .= "you (<b>".$currentuser."</b>) in another ";
        $inform_message .= "window (id: <b>";
        $inform_message .= $currentwindowid."</b>)</p>";
     }
  } else {
     if (!$quiet) {
        $inform_message .= "<p>Failed to confirm edit lock on the '".$tabname;
        $inform_message .= "' database.  It is now being edited by ";
        $inform_message .= "<b>".$currentuser."</b></p>";
     }
  }     

  return FALSE;

}



?>