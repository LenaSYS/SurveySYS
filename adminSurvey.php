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
	
	//------------------------------------------------------------------------------------------------
	// getOP
	//------------------------------------------------------------------------------------------------

	function getOP($name)
	{
			if(isset($_POST[$name]))	return $_POST[$name];
			else return "UNK";
	}

	//------------------------------------------------------------------------------------------------

	$crename=getOP('crename');
	$desc=getOP('desc');
	$admincode=getOP('admincode');
		
	date_default_timezone_set('Europe/Stockholm');
	
	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);	
	
	if($crename!="UNK"){
			echo "CREATING";
	}else{
			echo "NOT CREATING";	
	}

?>
		
		</body>

</html>