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
	
	$log_db = new PDO('sqlite:./scheduledata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS sched(id INTEGER PRIMARY KEY,datum varchar(10), datan TEXT);';
	$log_db->exec($sql);	
	
	if($crename!="UNK"){
			echo "CREATING";
	}else{
			echo "NOT CREATING";	
	}

?>
		
		</body>

</html>