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

  h3 {
      text-align:center;
  }
  .survey{
      margin:auto;
      padding:20px;
      box-shadow:4px 4px 10px #000;
      border-radius:6px;
  }

  .survey table {
      margin:auto;
  }

  .survey > tbody > tr:nth-child(odd){
      background-color:#fafafa;
  }
  .survey > tbody > tr:nth-child(even){
      background-color:#afafaf;
  }

  .survey > tfoot {
      font-size:10;
      text-align:right;
      color:rgba(0,0,0,0.5);
  }

	</style>

	<script>
		
		function randomString(string_length) {
				var chars = "0123456789ABCDEFGHIJKLMNOPQRSTUVWXTZabcdefghiklmnopqrstuvwxyz";
				var randomstring = '';
				for (var i=0; i<string_length; i++) {
					var rnum = Math.floor(Math.random() * chars.length);
					randomstring += chars.substring(rnum,rnum+1);
				}
				return randomstring;
		}		
		
		function init()
		{
				var userhash=randomString(8);
				if (localStorage.getItem("userhash") === null) {
						localStorage.setItem("userhash", userhash);
				}else{
						userhash=localStorage.getItem("userhash");
				}
			
				document.getElementById('userhash').value=userhash;
		}
		
	</script>

	</head>
	
	<body onload="init();">
		
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
			
	$hash=getOPG('hash');

  $log_db = new PDO('sqlite:./surveydata.db');
	$sql = 'CREATE TABLE IF NOT EXISTS survey(id INTEGER PRIMARY KEY,hash varchar(32),name varchar(64), description TEXT, admincode varchar(10));';
	$log_db->exec($sql);
	$sql = 'CREATE TABLE IF NOT EXISTS item(id INTEGER PRIMARY KEY,hash VARCHAR(32),questno INTEGER,labelA text, labelB text, labelC text, description TEXT, type INTEGER);';		
	$log_db->exec($sql);	
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,resphash VARCHAR(32),hash VARCHAR(32),questno INTEGER, itemid INTEGER, val TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);
	// Check if resphash column exists, add it if not exists
	$query=$log_db->prepare('PRAGMA table_info("response");');
	if (!$query->execute()) {
			$error = $log_db->errorInfo();
			print_r($error);
	}else{
      $hasResphash=false;
			$rows = $query->fetchAll(PDO::FETCH_ASSOC);	
			foreach($rows as $row){
					if($row["name"]==="resphash")$hasResphash=true;
      }
      if(!$hasResphash){
          $sql = 'ALTER TABLE response ADD COLUMN resphash VARCHAR(32);';		
          $log_db->exec($sql);	      
      }
	}
		
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
      $_SESSION['surveyname']=$datarow["name"];
      $_SESSION['surveydescription']=$datarow["description"];
			// Retrieve full database and swizzle into associative array for each day
			$query=$log_db->prepare('SELECT * FROM item where hash=:hash order by questno;');
			$query->bindParam(':hash', $hash);
			if (!$query->execute()) {
					$error = $log_db->errorInfo();
					print_r($error);
			}else{
				
					echo "<form method='post' action='saveSurvey.php' >";
				
					//echo "<table>";
					//echo "<tr><th>Rowno</th><th>Type</th><th>Labels (Left Right Center)</th><th>Description/Question</th></tr>";

					$lastrow='UNK';
					$rows = $query->fetchAll();	
			

					echo "<h3>Survey</h3>";	
				
          echo "<table class='survey'>";
          //echo "<caption>".$_SESSION['surveyname']."</caption>";
          echo "<thead>";
          echo "<tr>";
          echo "<th>";
          echo $_SESSION['surveyname'];
          echo "</th>";
          echo "</tr>";
          echo "<tr>";
          echo "<th>";
          echo $_SESSION['surveydescription'];
          echo "</th>";
          echo "</tr>";
          echo "</thead>";
          echo "<tbody>";
					foreach($rows as $row){              
							if($row['type']==2){
                  echo "<tr>";
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
                  echo "</tr>";
              }else if($row['type']==3){
                  echo "<tr>";
                  echo "<td><table>";

                  // Question / Description
                  echo "<tr><td colspan='2'>".$row['description']."</td></tr>";
                
                  // Text Input with Labels
                  echo "<td>".$row['labelA'].":</td><td><input type='text' name='qq_".$row['id']."_".$row['questno']."' value='".$row['labelC']."'></td>";
                  
                  echo "</table></td>";
                  echo "</tr>";
              }else if($row['type']==1){
                  echo "<tr>";
                  echo "<td><table>";

                  // URL
                  echo "<tr><td colspan='2'>Visit <a href='".$row['description']."' target='_blank'>".$row['description']."</a> and answer the questions below.</td></tr>";                                  
                  echo "</table></td>";
                  echo "</tr>";
              }
					}
          echo "</tbody>";
          echo "<tfoot>";
          echo "<tr>";
          echo "<th>";
          $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
          echo "Survey URL: <a href='".$actual_link."'>".$actual_link."</a>";
          echo "</th>";
          echo "</tr>";
          echo "</tfoot>";
					echo "<tr><td style='text-align:right'><input type='submit' value='Save'></td></tr>";
					echo "</table>";				
					echo "<input type='hidden' name='userhash' id='userhash' value=''>";
					echo "<input type='hidden' name='hash' value='".$hash."'>";				
					echo "</form>";				
			}
		
	}else{
			echo "Unknown survey: ".$hash;
	}
		
?>
		
</body>

</html>