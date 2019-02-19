<html>
 
	<head>

	<style>

	body{
			font-family: Arial Narrow,Arial,sans-serif; 
			font-size:16px;
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

	echo "<pre>";
	print_r($_POST);
	echo "</pre>\n";
		
	// Command
	$cmd=getOP('CMD');

	// Parameters
	$crename=getOP('crename');
	$desc=getOP('desc');
	$admincode=getOP('admincode');
		
	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);	
	
	if($cmd!="UNK"){
		
			// Insert new survey data
			if($cmd="NEW"){
					echo "Making new survey";
			}
		
			// Make survvey administration form 
			echo "<form method='POST' name='editSurvey' action='adminSurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='EDIT' >\n";
			echo "<table>\n";
			echo "<tr><td>Name:</td><td><input type='text' name='crename' value='".$crename."' ></td></tr>\n";
			echo "</table>\n";
			echo "<input type='submit' value='Save Survey' >\n";
			echo "</form>\n";
	}else{
			// Make survvey administration form 
			echo "<form method='POST' name='editSurvey' action='adminSurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='NEW' >\n";
			echo "<table>\n";
			echo "<tr><td>Name:</td><td><input type='text' name='crename' value='New Survey Name' ></td></tr>\n";
			echo "</table>\n";
			echo "<input type='submit' value='Create Survey' >\n";
			echo "</form>\n";
	}
		
?>
		
		</body>

</html>