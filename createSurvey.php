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
		
	/* #admincode {		
			border: 2px solid red;
			border-radius: 6px;
			margin: 8px;
			padding: 8px;			
	} */
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
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER,labelA text, labelB text, labelC text, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,resphash VARCHAR(32),hash VARCHAR(32),questno INTEGER, itemid INTEGER, val TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);		
  
	if($cmd!="UNK"){
		
			// Insert new survey data
			if($cmd=="NEW"){
					$query = $log_db->prepare('INSERT INTO survey(hash,name,description,admincode) VALUES (:hash,:name,:description,:admincode)');
					
					$query->bindParam(':hash', $hash);
					$query->bindParam(':name', $crename);
					$query->bindParam(':description', $desc);
					$query->bindParam(':admincode', $admincode);				
				
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}
					echo "<div style='width: 600px; margin:auto'>";		
					echo "<h2>New Servey created</h2>";
					echo "<div id='admincode'>";
					echo "<p>Notera informationen nedan på ett säkert ställe. Du behöver <strong>survey hash</strong> och <strong>admin code</strong> för att kunna administrera din survey.</p>";
					echo "<table>";
					echo "<tr><td>New Survey Created: ".$crename."</td></tr>";
					echo "<tr><td>Survey Hash: ".$hash."</td></tr>";
					echo "<tr><td>Admin Code: ".$admincode."</td></tr>";
					echo "</table>";
					echo "</div>";
					echo "<a href='editSurvey.php'>Edit survey</a>";
					echo "</div>";
			}
	}else{
			// Make survvey administration form 
			echo "<div style='width: 600px; margin:auto'>";
			echo "<h2>Create new survey</h2>";
			echo "<form method='POST' name='editSurvey' action='createSurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='NEW' >\n";
			echo "<div style='margin-bottom:2em;'>";
			echo "<label style='display:block;' for='crename'>Name</label>";
			echo "<input style='width:100%;' id='crename' type='text' name='crename' placeholder='New Survey Name' >";
			echo "</div>";
			echo "<div style='margin-bottom:2em;'>";
			echo "<label style='display:block' for='desc'>Description</label>";
			echo "<textarea style='width:100%;' id='desc' rows='8' cols='40' name='desc' ></textarea>";
			echo "</div>";
			echo "<div style='margin-bottom:2em; display:flex; flex-wrap:nowrap; justify-content:space-between'>";
			echo "<a href='editSurvey.php'>Edit existing survey</a>";
			echo "<input type='submit' value='Create Survey' >\n";
			echo "</div>";
			echo "</form>\n";
			echo "</div>";
	}

			// Make survvey administration form 
			// echo "<div id='admincode'>\n";
			// echo "Enter survey hash and administration code to edit an existing survey.<br>";
			// echo "<em>Please keep a copy of hash and administration code for future use, without these, it is not possible to edit the survey.</em>";
			// echo "<form method='POST' name='editSurvey' action='editSurvey.php' >\n";
			// echo "<input type='hidden' name='CMD' value='EDIT' >\n";
			// echo "<table>\n";
			// echo "<tr><td>Hash:</td><td><input type='text' placeholder='Enter Hash' name='hash' ></td></tr>\n";
			// echo "<tr><td>Code:</td><td><input type='text' placeholder='Admin Code' name='admincode' ></td></tr>\n";		
			// echo "</table>\n";
			// echo "<input type='submit' value='Edit Survey' >\n";
			// echo "</form>\n";
			// echo "</div>\n";
		
?>
		
		</body>

</html>