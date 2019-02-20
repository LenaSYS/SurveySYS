<?php
// Start the session
session_start();
?>
<html>
 
	<head>

	<style>

	body{
			font-family: Arial Narrow,Arial,sans-serif; 
			font-size:16px;
	}
		
	#admincode {
		
			border: 2px solid red;
			border-radius: 6px;
			margin: 8px;
			padding: 8px;
			
	}

	</style>

	<script>
	</script>

	</head>
	
	<body>
		
<?php			

	date_default_timezone_set('Europe/Stockholm');

	//------------------------------------------------------------------------------------------------
	// getOP
	//------------------------------------------------------------------------------------------------

	function getOP($name)
	{
			if(isset($_POST[$name]))	return $_POST[$name];
			else return "UNK";
	}

	//------------------------------------------------------------------------------------------------
	// randomString
	//------------------------------------------------------------------------------------------------

	function randomString($length = 10) {
			$characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
			$charactersLength = strlen($characters);
			$randomString = '';
			for ($i = 0; $i < $length; $i++) {
					$randomString .= $characters[rand(0, $charactersLength - 1)];
			}
			return $randomString;
	}		

	//------------------------------------------------------------------------------------------------
		
	echo "<pre>";
	print_r($_POST);
	echo "</pre>\n";
		
	// Command
	$cmd=getOP('CMD');

	// Parameters
	$crename=getOP('crename');
	$desc=getOP('desc');
	$admincode=getOP('admincode');
	$hash=randomString(8);
	$admincode=randomString(8);
		
	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);			
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER, type INTEGER, value TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	
	
	if($cmd!="UNK"){
		
			// Insert new survey data
			if($cmd=="NEW"){
					echo "Making new survey";
					$query = $log_db->prepare('INSERT INTO survey(hash,name,description,admincode) VALUES (:hash,:name,:description,:admincode)');
					
					$query->bindParam(':hash', $hash);
					$query->bindParam(':name', $crename);
					$query->bindParam(':description', $desc);
					$query->bindParam(':admincode', $admincode);				
				
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}

					echo "<div id='admincode'>\n";
					echo "<table>\n";
					echo "<tr><td>New Survey Created: ".$crename."</td></tr>";
					echo "<tr><td>Survey Hash: ".$hash."</td></tr>";
					echo "<tr><td>Admin Code: ".$admincode."</td></tr>";
					echo "</table>\n";
					echo "</div>\n";
			}
	}else{
			// Make survvey administration form 
			echo "<form method='POST' name='editSurvey' action='createSurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='NEW' >\n";
			echo "<table>\n";
			echo "<tr><td>Name:</td><td><input type='text' name='crename' value='New Survey Name' ></td></tr>\n";
			echo "<tr><td>Description</td><td>";
			echo "<textarea rows='8' cols='40' name='desc' >";
			echo "</textarea>";
			echo "</tr></td>";
			echo "</table>\n";
			echo "<input type='submit' value='Create Survey' >\n";
			echo "</form>\n";
	}

			// Make survvey administration form 
			echo "<div id='admincode'>\n";
			echo "<form method='POST' name='editSurvey' action='editsurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='EDIT' >\n";
			echo "<table>\n";
			echo "<tr><td>Hash:</td><td><input type='text' value='Enter Hash' name='hash' ></td></tr>\n";
			echo "<tr><td>Code:</td><td><input type='text' value='Admin Code' name='admincode' ></td></tr>\n";		
			echo "</table>\n";
			echo "<input type='submit' value='Edit Survey' >\n";
			echo "</form>\n";
			echo "</div>\n";
		
?>
		
		</body>

</html>