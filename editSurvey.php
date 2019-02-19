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
		
	$hash=getOP('hash');
	$admincode=getOP('admincode');
	if(isset($_SESSION['hash'])) $login=$_SESSION['hash'];
	if(isset($_SESSION['admincode'])) $password=$_SESSION['admincode'];	

	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);
		
	$datarow=Array();

	// Retrieve full database and swizzle into associative array for each day
	$result = $log_db->query('SELECT * FROM survey where hash=:hash and admincode=:admincode;');
	$query->bindParam(':hash', $hash);
	$query->bindParam(':admincode', $admincode);		
	if (!$query->execute()) {
			$error = $log_db->errorInfo();
			print_r($error);
	}else{
			$rows = $result->fetchAll();	
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

			echo "Kloo!";
	}	
		
?>
		
</body>

</html>