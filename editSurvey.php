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

/*
	echo "<pre>";
	print_r($_POST);
	echo "</pre>\n";
		
	echo "<pre>";
	print_r($_SESSION);
	echo "</pre>\n";		
*/
		
	$description=getOP('description');
	$labelA=getOP('labelA');
	$labelB=getOP('labelB');		
	$labelC=getOP('labelC');
	$type=getOP('type');
		
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

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
		
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
		
	if(sizeof($datarow)>0){
		
			echo "Survey: ".$hash."<br>";
		
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
				
					//$query = $log_db->prepare('INSERT INTO item(hash,description,type,labelA,labelB,labelC,questno) VALUES (:hash,:description,:type,:labelA,:labelB,:labelC,(select count(*) from item where hash=:hasho)+1)');
					$query = $log_db->prepare('INSERT INTO item(hash,description,type,labelA,labelB,labelC,questno) VALUES (:hash,:description,:type,:labelA,:labelB,:labelC,(select count(*) from item where hash=:hasho)+1)');					
					$query->bindParam(':hash', $hash);
					$query->bindParam(':hasho', $hash);				
					$query->bindParam(':labelA', $labelA);
					$query->bindParam(':labelB', $labelB);
					$query->bindParam(':labelC', $labelC);
					$query->bindParam(':type', $type);
					$query->bindParam(':description', $description);				
				
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}
			}
		
			// Retrieve full database and swizzle into associative array for each day
			$query=$log_db->prepare('SELECT * FROM item where hash=:hash order by questno;');
			$query->bindParam(':hash', $hash);
			if (!$query->execute()) {
					$error = $log_db->errorInfo();
					print_r($error);
			}else{
				
					echo "<table>";
					echo "<tr><th>Rowno</th><th>Type</th><th>Labels (Left Right Center)</th><th>Description/Question</th></tr>";

					$lastrow='UNK';
					$rows = $query->fetchAll();	
					foreach($rows as $row){
								echo "<tr>";
								
								// Number
								echo "<td>".$row['questno']."</td>";
								
								// Type
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'><select name='type'>";
								if($row['type']==1){
										echo "<option value='1' selected='selected'>Link</option><option value='2'>Number</option><option value='3'>Text</option></select>";		
								}else if($row['type']==2){
											echo "<option value='1'>Link</option><option value='2' selected='selected'>Number</option><option value='3'>Text</option></select>";		
								}else if($row['type']==3){
											echo "<option value='1'>Link</option><option value='2'>Number</option><option value='3' selected='selected'>Text</option></select>";		
								}else{
										echo "Unknown type: ".$row['type'];
								}
								echo "</select>";
								echo "<input type='hidden' name='CMD' value='UPDTYPE'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";

								// Labels
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<input type='text' name='labelA' value='".$row['labelA']."' >";
								echo "<input type='text' name='labelB' value='".$row['labelB']."' >";
								echo "<input type='text' name='labelC' value='".$row['labelC']."' >";						
								echo "<input type='hidden' name='CMD' value='UPDLABEL'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";
						
								// Description
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<textarea name='description' >".$row['description']."</textarea>";				
								echo "<input type='hidden' name='CMD' value='UPDDESC'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";								

								// Swap
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<input type='hidden' name='CMD' value='SWAP'>";
								if($lastrow!="UNK"){
										echo "<input type='submit' value='Swap' >\n";
										echo "<input type='hidden' name='Swap' value='".$lastrow."'>";
										echo "<input type='hidden' name='SwapOut' value='".$row['id']."'>";									
								}
								echo "</form></td>";

								// Del
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<input type='hidden' name='CMD' value='DEL'>";
								echo "<input type='submit' value='Del' >\n";
								echo "</form></td>";
						
								echo "</tr>";
						
								$lastrow=$row['id'];
					}
					echo "</table>";

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
											echo "<td><input type='radio' name='qq_".$row['id']."_".$row['questno']."' value='".$i."'></td>";
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
									echo "<td>".$row['labelA'].":</td><td><input type='text' name='qq_".$row['id']."_".$row['questno']."' value='".$row['labelC']."'></td>";
									
									echo "</table></td>";
							}
							echo "</tr><tr></tr>";
					}
				
					echo "</table>";
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