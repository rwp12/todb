<html>
<head></head>
<body>

<form method="post" action="" name="setupdb">
Please enter your db root password.  This will be needed to create your database:</br/>
Root password: <input type="password" name="dbroot" id="dbroot" value=""><br/>
<br><br/>
Please enter a host and name for your database.  
This should be a short string (alphabet characters only) that identifies your site</br/>
Database host: <input type="text" name="dbhost" id="dbhost" value="hostname"><br/>
Database name: <input type="text" name="dbname" id="dbname" value="todb"><br/>
<br><br/>
You will now be prompted for database passwords to use for read (non-edit) and write access.</br>

Read Password: <input type="text" name="readpass" id="readpass" value="bobread"><br/>
Read password confirm: <input type="text" name="readpasswordconfirm" id="readpasswordconfirm" value="bobread"><br/>
<br><br/>
Write Password: <input type="text" name="writepass" id="writepass" value="bobwrite"><br/>
Write password confirm: <input type="text" name="writepasswordconfirm" id="writepasswordconfirm" value="bobwrite"><br/>
<br><br/>
<input type="submit"/>
</form>


<?php 

$dbroot = isset($_POST['dbroot']) ? stripslashes($_POST['dbroot']) : '';
$dbhost = isset($_POST['dbhost']) ? stripslashes($_POST['dbhost']) : '';
$dbname = isset($_POST['dbname']) ? stripslashes($_POST['dbname']) : '';

$readpass = isset($_POST['readpass']) ? stripslashes($_POST['readpass']) : '';
$readpasswordconfirm = isset($_POST['readpasswordconfirm']) ? stripslashes($_POST['readpasswordconfirm']) : '';

$readuser = $dbname.'_read';

$createreaduser = "CREATE USER '".$readuser."'@'".$dbhost."' IDENTIFIED BY '".$readpass."'";
$grantreaduser = "GRANT SELECT, LOCK TABLES ON ".$dbname.".* TO '".$readuser."'@'".$dbhost."'  IDENTIFIED BY '".$readpass."'";

$writepass = isset($_POST['writepass']) ? stripslashes($_POST['writepass']) : '';
$writepasswordconfirm = isset($_POST['writepasswordconfirm']) ? stripslashes($_POST['writepasswordconfirm']) : '';
  
$writeuser = $dbname.'_write';

$createwriteuser = "CREATE USER '".$writeuser."'@'".$dbhost."' IDENTIFIED BY '".$writepass."'";
$grantwriteuser = "GRANT ALL PRIVILEGES ON ".$dbname.".* TO '".$writeuser."'@'".$dbhost."'  IDENTIFIED BY '".$writepass."'";


if(($dbname != "") && ($writeuser != "") && ($readpass != "") && ($readpass == $readpasswordconfirm) 
&& ($writepass != "") && ($writepass == $writepasswordconfirm))
{
	$db_ok = true;
	$con = mysql_connect($dbhost,"root",$dbroot);
	if (!$con)
	  {
	  die('Could not connect: ' . mysql_error());
	  $db_ok = false;
	  }

	$dbcreate = "CREATE DATABASE ".$dbname;
	if (mysql_query($dbcreate,$con))
	  {
	  echo "Database created ".$dbname;
	  	if(mysql_query($createreaduser,$con)){
		
	  		echo "<p>Read user created ".$readuser."</p>";
	  		
			if(mysql_query($grantreaduser,$con)){
	  			echo "<p>Read user privileges granted ".$grantreaduser."</p>";
				//mysql_query("flush privileges",$con);
			}
			else
	  		{
	  			echo "<p>Error creating read user: " . mysql_error()."</p>";
	  			$db_ok = false;
	  		}
		}
		else
	  	{
	  		echo "<p>Error granting read user privileges: " . mysql_error()."</p>";
	  		$db_ok = false;
	  	}
	  
		if(mysql_query($createwriteuser,$con)){
		
	  		echo "<p>Write user created ".$writeuser."</p>";
	  		
			if(mysql_query($grantwriteuser,$con)){
	  			echo "<p>Write user privileges granted ".$grantwriteuser."</p>";
				mysql_query("flush privileges",$con);
			}
			else
	  		{
	  			echo "<p>Error write creating user: " . mysql_error()."</p>";
	  			$db_ok = false;
	  		}
		}
	else
  	{
  		echo "<p>Error granting user privileges: " . mysql_error()."</p>";
  		$db_ok = false;
  	}
  }
else
  {
  echo "Please fill in some data and click on the submit button";
  $db_ok = false;
  }
  
if ($db_ok)  {
	
   if ($dbroot != '') {
	   	$cmd = 'mysql '.$dbname.' -h '.$dbhost.' -u root --password='.$dbroot.' < SQL/create_TODB_tables.sql';
     
   } else 
   {
       $cmd = 'mysql '.$dbname.' -h '.$dbhost.' -u root < SQL/create_TODB_tables.sql';
   
   } 
	    
 		exec($cmd,$out,$retval);
 	
 		if (!$retval) {
 				echo "<p>Database tables created ".$database_script."</p>";
				
			}
			else
	  		{
	  			echo "<p>Error creating database tables: " . mysql_error()."</p>";
	  			$db_ok = false;
	  		}/*
 	}
	else {
		echo "<p>Error using database: ". mysql_error()."</p>";
	}*/
}

  
//.. etc etc
mysql_close($con);

//create db.inc file
if ($db_ok)
{
	$myFile = "config/db.inc"; //need to get the relative file path correct
	$fh = fopen($myFile, 'w');
	
	if (!$fh) {
	    echo "<p>Error opening db.inc</p>";
	}
	else {
		fwrite($fh, "<?php");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // MJ, 20090721:");
		fwrite($fh, "\n // Please set database connection information here:");
		fwrite($fh, "\n // ------------------------------------------------");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // Database name (on localhost)");
		fwrite($fh, "\n \$database_host = '". $dbhost."';");
		fwrite($fh, "\n \$database_name = '". $dbname."';");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // Read user details");
		fwrite($fh, "\n \$readuser_name = '". $readuser."';");
		fwrite($fh, "\n \$readuser_pass = '". $readpass."';");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // Write user details");
		fwrite($fh, "\n \$writeuser_name = '". $writeuser."';");
		fwrite($fh, "\n \$writeuser_pass = '". $writepass."';");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // ------------------------------------------");
		fwrite($fh, "\n ");
		fwrite($fh, "\n // include the database connection apparatus");
		fwrite($fh, "\n // DO NOT remove this line!");
		fwrite($fh, "\n // NOTE: this file is stored in the parent directory.");
		fwrite($fh, "\n \$path = substr(\$_SERVER['SCRIPT_FILENAME'], 0, strrpos(\$_SERVER['SCRIPT_FILENAME'], '/' ));");
		fwrite($fh, "\n require (\$path.'/dbconnect.inc');");
		fwrite($fh, "\n ");
		fwrite($fh, "\n ?>");
		
		fclose($fh);
		echo "<p>".$myFile. " file successfully created</p>";
	}

}
}
else{
echo "Please fill in some data and click on the submit button";
}

?>

</body>

</html>