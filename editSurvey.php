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

	echo "<pre>";
	print_r($_POST);
	echo "</pre>\n";
		
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>\n";		

	$cmd=getOP('CMD');
	$hash=getOP('hash');
	$admincode=getOP('admincode');
	if(isset($_SESSION['hash'])) $hash=$_SESSION['hash'];
	if(isset($_SESSION['admincode'])) $admincode=$_SESSION['admincode'];	

	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER,labelA text, labelB text, labelC text, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER, value TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	
		
	$datarow=Array();

	// Retrieve full database and swizzle into associative array for each day
	$query=$log_db->prepare('SELECT * FROM survey where hash=:hash and admincode=:admincode;');
	$query->bindParam(':hash', $hash);
	$query->bindParam(':admincode', $admincode);		
	if (!$query->execute()) {
			$error = $log_db->errorInfo();
			print_r($error);
	}else{
			$rows = $query->fetchAll();	
			foreach($rows as $row){
					$datarow=$row;
			}
	}
		
	echo "<pre>";
	print_r($datarow);
	echo "</pre>\n";	
		
	if(sizeof($datarow)>0){
			$_SESSION['hash']=$hash;
			$_SESSION['admincode']=$admincode;

			echo "<div id='admincode'>\n";
			echo "<form method='POST' name='editSurvey' action='editsurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='NEW' >\n";
			echo "<table>\n";
			echo "<tr><td>Type:</td><td><select name='type'><option value='1'>Link</option><option value='2'>Number</option><option value='3'>Text</option></select></td></tr>\n";		
			echo "<tr><td>Question:</td><td><input type='text' name='description'></td></tr>\n";		
			echo "<tr><td>Left Label:</td><td><input type='text' name='labelA'></td></tr>\n";		
			echo "<tr><td>Right Label:</td><td><input type='text' name='labelB'></td></tr>\n";
			echo "<tr><td>Center Label:</td><td><input type='text' name='labelC'></td></tr>\n";		
			echo "</table>\n";
			echo "<input type='submit' value='New Item' >\n";
			echo "</form>\n";
			echo "</div>\n";
		
			if($cmd=="NEW"){
					echo "MAKING NEW!!!";
			}

			
	}else{
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
	}
		
?>
		
</body>

</html>