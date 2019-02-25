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
		
	form {
			margin:0px;
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
  
  $log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER,labelA text, labelB text, labelC text, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER, itemid INTEGER, val TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);

	foreach($_POST as $key=>$val){
			//echo $key;
			if($key!='hash'){
					$lst=explode("_",$key);
					
          echo "<pre>".$lst[1]." ".$lst[2]." ".$val."</pre>";
          $query = $log_db->prepare('INSERT INTO response(hash,questno,itemid,val,useragent,userhash) VALUES (:hash,:questionno,:itemid,:val,:useragent,:userhash);');					
					$query->bindParam(':hash', $hash);
					$query->bindParam(':questno', $lst[2]);				
					$query->bindParam(':itemid', $lst[1]);
					$query->bindParam(':val', $val);
					$query->bindParam(':useragent', $lst[3]);
					$query->bindParam(':userhash', $lst[4]);
				
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error inserting survey answer:\n\n\n " . $error[2];
					}
			}
  }	
  

	
?>
		
</body>

</html>