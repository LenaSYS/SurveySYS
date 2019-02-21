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

	function getOPG($name)
	{
			if(isset($_GET[$name]))	return $_GET[$name];
			else return "UNK";
	}

	//------------------------------------------------------------------------------------------------

	echo "<pre>";
	print_r($_GET);
	echo "</pre>\n";
				
	$hash=getOPG('hash');

	$log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER,labelA text, labelB text, labelC text, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER, value TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
	$datarow=Array();

	// Retrieve full database and swizzle into associative array for each day
	$query=$log_db->prepare('SELECT * FROM survey where hash=:hash;');
	$query->bindParam(':hash', $hash);		
	if (!$query->execute()) {
			$error = $log_db->errorInfo();
			print_r($error);
	}else{
			$rows = $query->fetchAll();	
			foreach($rows as $row){
					$datarow=$row;
			}
	}
		
	if(sizeof($datarow)>0){
		
					// Retrieve full database and swizzle into associative array for each day
			$query=$log_db->prepare('SELECT * FROM item where hash=:hash order by questno;');
			$query->bindParam(':hash', $hash);
			if (!$query->execute()) {
					$error = $log_db->errorInfo();
					print_r($error);
			}else{
				
					echo "<form method='post' action='saveSurvey.php' >";
				
					echo "<table>";
					echo "<tr><th>Rowno</th><th>Type</th><th>Labels (Left Right Center)</th><th>Description/Question</th></tr>";

					$lastrow='UNK';
					$rows = $query->fetchAll();	
			

					echo "<h3>Preview</h3>";	
				
					// Preview
					echo "<table>";
	
					foreach($rows as $row){
							echo "<tr>";
							if($row['type']==2){
									echo "<td>";
									echo "<table>";
									
									// Question / Description
									echo "<tr><td colspan='7'>".$row['description']."</td></tr>";
									
									// Radio Buttons
									echo "<tr>";
									for($i=1;$i<8;$i++){
											echo "<td><input type='radio' name='qq".$row['id']."' value='".$i."'></td>";
									}
									echo "</tr>";
								
									// Labels
									echo "<tr>";
									echo "<td style='text-align:left;' colspan='2'>".$row['labelA']."</td>";
									echo "<td style='text-align:center;' colspan='3'>".$row['labelC']."</td>";
									echo "<td style='text-align:right;' colspan='2'>".$row['labelB']."</td>";								
									echo "</tr>";
								
									echo "</table>";
									echo "</td>";
							}else if($row['type']==3){
									echo "<td><table>";

									// Question / Description
									echo "<tr><td colspan='2'>".$row['description']."</td></tr>";
								
									// Text Input with Labels
									echo "<td>".$row['labelA'].":</td><td><input type='text' name='qq".$row['id']."' value='".$row['labelC']."'></td>";
									
									echo "</table></td>";
							}
							echo "</tr><tr></tr>";
					}
				
					echo "<tr><td><input type='submit' value='Save'></td></tr>";
				
					echo "</table>";
				
					echo "</form>";

				
			}
		
	}else{
			echo "Unknown survey: ".$hash;
	}
		
?>
		
</body>

</html>