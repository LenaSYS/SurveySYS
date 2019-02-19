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
	
	#leftover {
			position:absolute;
			right:0px;
			top:0px;
			bottom:0px;
			width:300px;
			background:#def;
			overflow:scroll;
	}

</style>

<script>
	
	function clickbutt()
	{
			localStorage.setItem("Startdatum",document.getElementById("Startdatum").value);
			localStorage.setItem("Starttid",document.getElementById("Starttid").value);
			localStorage.setItem("Slutdatum",document.getElementById("Slutdatum").value);
			localStorage.setItem("Sluttid",document.getElementById("Sluttid").value);
			localStorage.setItem("Lokal",document.getElementById("Lokal").value);
			localStorage.setItem("Benamning",document.getElementById("Benamning").value);
			localStorage.setItem("Grupp",document.getElementById("Grupp").value);
			localStorage.setItem("Tillfalle",document.getElementById("Tillfalle").value);
			localStorage.setItem("Signatur",document.getElementById("Signatur").value);
			localStorage.setItem("Aktivitet",document.getElementById("Aktivitet").value);
			localStorage.setItem("Kommentar",document.getElementById("Kommentar").value);
	}
	
	function loaded()
	{
			if (localStorage.getItem("Startdatum") !== null) document.getElementById("Startdatum").value=localStorage.getItem("Startdatum"); 
			if (localStorage.getItem("Starttid") !== null) document.getElementById("Starttid").value=localStorage.getItem("Starttid"); 		
			if (localStorage.getItem("Slutdatum") !== null) document.getElementById("Slutdatum").value=localStorage.getItem("Slutdatum"); 
			if (localStorage.getItem("Sluttid") !== null) document.getElementById("Sluttid").value=localStorage.getItem("Sluttid"); 		
			if (localStorage.getItem("Lokal") !== null) document.getElementById("Lokal").value=localStorage.getItem("Lokal"); 
			if (localStorage.getItem("Benamning") !== null) document.getElementById("Benamning").value=localStorage.getItem("Benamning"); 		
			if (localStorage.getItem("Grupp") !== null) document.getElementById("Grupp").value=localStorage.getItem("Grupp"); 
			if (localStorage.getItem("Tillfalle") !== null) document.getElementById("Tillfalle").value=localStorage.getItem("Tillfalle"); 		
			if (localStorage.getItem("Signatur") !== null) document.getElementById("Signatur").value=localStorage.getItem("Signatur"); 		
			if (localStorage.getItem("Aktivitet") !== null) document.getElementById("Aktivitet").value=localStorage.getItem("Aktivitet"); 
			if (localStorage.getItem("Kommentar") !== null) document.getElementById("Kommentar").value=localStorage.getItem("Kommentar"); 		
	}
	
</script>

</head>
<body onload='loaded();' >
		
<?php			

		//------------------------------------------------------------------------------------------------
		// getOP
		//------------------------------------------------------------------------------------------------

		function getOP($name)
		{
				if(isset($_POST[$name]))	return $_POST[$name];
				else return "UNK";
		}

		date_default_timezone_set('Europe/Stockholm');

		$log_db = new PDO('sqlite:./scheduledata.db');
		$sql = 'CREATE TABLE IF NOT EXISTS sched(id INTEGER PRIMARY KEY,datum varchar(10), datan TEXT);';
		$log_db->exec($sql);	

		$startdatum=getOP('Startdatum');
		$starttid=getOP('Starttid');
		$slutdatum=getOP('Slutdatum');
		$sluttid=getOP('Sluttid');
		$lokal=getOP('Lokal');
		$benamning=getOP('Benamning');	
		$grupp=getOP('Grupp');	
		$tillfalle=getOP('Tillfalle');	
		$signatur=getOP('Signatur');	
		$aktivitet=getOP('Aktivitet');	
		$kommentar=getOP('Kommentar');	
		$cmd=getOP('cmd');

		$login=getOP('login');
		$password=getOP('password');
		if(isset($_SESSION['login'])) $login=$_SESSION['login'];
		if(isset($_SESSION['password'])) $password=$_SESSION['password'];	

		$hash='$2y$10$Db7twqkR/jtH0FnV157nl.w8vEO5ihtOUJzBnzZKXTXcY/Mt.Sw3C';
		$passwordhash=password_verify($password, $hash);
		// password_hash($password,PASSWORD_DEFAULT);
	
		if(!$passwordhash){
				$cmd="UNK";
		}else{
				if($cmd=="UNK") $cmd="SESSION";
		}
	
		if($cmd=="UNK"){
				echo "<form method='post' action='admin.php'>";
				echo "  <div>login:<input type='text' name='login'><input type='hidden' name='cmd' value='LOGIN'></div>";
				echo "  <div>passw:<input type='password' name='password'></div>";
				echo "  <button>ok</button>";		
				echo "</form>";
		}else if($cmd=="LOGOFF"){
				session_unset();
				session_destroy();
		}else{
				if($passwordhash){
						$_SESSION['login']=$login;
						$_SESSION['password']=$password;

						// Retrieve full database and swizzle into associative array for each day
						$dbarr= array();
						$result = $log_db->query('SELECT * FROM sched order by datum desc;');
						if (!$result) {
								$error = $log_db->errorInfo();
								print_r($error);
						}else{
								$rows = $result->fetchAll();	
								foreach($rows as $row){
										$dag=json_decode($row['datan'],true);
										$dbarr[$row['datum']]=$dag;
								}
						}
	
						// Make delete / Update before we show table
						if($startdatum!="UNK"&&$cmd=='SAVE'){
								$barr=Array();
								$item=Array();
								$item['Startdatum']=$startdatum;
								$item['Starttid']=$starttid;
								$item['Slutdatum']=$slutdatum;
								$item['Sluttid']=$sluttid;
								$item['Lokal']=$lokal;
								$item['Benamning']=urlencode($benamning);
								$item['Grupp']=$grupp;
								$item['Tillfalle']=$tillfalle;
								$item['Signatur']=$signatur;
								$item['Aktivitet']=urlencode($aktivitet);
								$item['Kommentar']=urlencode($kommentar);					

								// Add or update data
								if(isset($dbarr[$startdatum])){
										array_push($dbarr[$startdatum],$item);
										$datan=json_encode($dbarr[$startdatum]);
										$query = $log_db->prepare('UPDATE sched set datan=:datan where datum=:datum');
										$query->bindParam(':datum', $startdatum);
										$query->bindParam(':datan', $datan);
								}else{
										array_push($barr,$item);
										$datan=json_encode($barr);
										$query = $log_db->prepare('INSERT INTO sched(datum,datan) VALUES (:datum,:datan)');
										$query->bindParam(':datum', $startdatum);
										$query->bindParam(':datan', $datan);
								}
								if (!$query->execute()) {
										$error = $query->errorInfo();
										$debug = "Error reading files " . $error[2];
								}
						}else if($startdatum!="UNK"&&$cmd=='DEL'){
								if(isset($dbarr[$startdatum])){
										$barr=Array();
										$dag=$dbarr[$startdatum];
										foreach ($dag as $key => $dd) {
												if(($dd['Starttid']==$starttid)){

												}else{
														array_push($barr,$dd);
												}
										}
										$datan=json_encode($barr);
										echo $startdatum.$datan;
										$query = $log_db->prepare('UPDATE sched set datan=:datan where datum=:datum');
										$query->bindParam(':datum', $startdatum);
										$query->bindParam(':datan', $datan);									
										if (!$query->execute()) {
												$error = $query->errorInfo();
												$debug = "Error reading files " . $error[2];
										}
								}
						}
					
						// Make a form and present information
						echo "<form onsubmit='clickbutt();' method='post' action='admin.php'>";
						echo "<table>";
						echo "  <tr><td>Startdatum:</td><td><input type='date' name='Startdatum' id='Startdatum'></td></tr>";
						echo "  <tr><td>Starttid:</td><td><input type='time' name='Starttid' id='Starttid'></td></tr>";			
						echo "  <tr><td>Slutdatum:</td><td><input type='date' name='Slutdatum' id='Slutdatum'></td></tr>";
						echo "  <tr><td>Sluttid:</td><td><input type='time' name='Sluttid' id='Sluttid'></td></tr>";			
						echo "  <tr><td>Lokal:</td><td><input type='text' name='Lokal' id='Lokal'></td></tr>";			
						echo "  <tr><td>Benamning:</td><td><input type='text' name='Benamning' id='Benamning'></td></tr>";			
						echo "  <tr><td>Grupp:</td><td><input type='text' name='Grupp' id='Grupp'></td></tr>";			
						echo "  <tr><td>Tillfalle:</td><td><input type='text' name='Tillfalle' id='Tillfalle'></td></tr>";			
						echo "  <tr><td>Signatur:</td><td><input type='text' name='Signatur' id='Signatur'></td></tr>";			
						echo "  <tr><td>Aktivitet:</td><td><input type='text' name='Aktivitet' id='Aktivitet'></td></tr>";			
						echo "  <tr><td>Kommentar:</td><td><input type='text' name='Kommentar' id='Kommentar'></td></tr>";			
						echo "  <tr><td><input type='hidden' name='cmd' value='SAVE'></td></tr>";
						echo "  <tr><td><button>Store</button></td></tr>";		
						echo "</table>";
						echo "</form>";
					
						// Show editing interface for updated database
						$dbarr= array();
						$result = $log_db->query('SELECT * FROM sched order by datum desc;');
						if (!$result) {
								$error = $log_db->errorInfo();
								print_r($error);
						}else{
								$rows = $result->fetchAll();	
								// Add interface for deleting elements
								echo "<div id='leftOver'><table>";
								foreach($rows as $row){
										$dag=json_decode($row['datan'],true);
										foreach ($dag as $key => $dd) {
												if(isset($dd['Starttid'])){
														echo "<tr>";
														echo "<td>".$row['datum']."</td>";
														echo "<td>".$dd['Starttid']."</td>";
														echo "<td>-</td>";	
														echo "<td>".$dd['Sluttid']."</td>";
													  echo "<td>".urldecode(substr($dd['Benamning'],0,12))."</td>";
														echo "<td>";
														echo "<form method='post' action='admin.php' style='margin:0px;padding:0px;'>";
														echo "<input type='hidden' name='cmd' value='DEL'>";
														echo "<input type='hidden' name='Startdatum' value='".$row['datum']."'>";
														echo "<input type='hidden' name='Starttid' value='".$dd['Starttid']."'>";
														echo "<input type='hidden' name='Sluttid' value='".$dd['Sluttid']."'>";	
														echo "<input type='submit' value='&#10008;'>";
														echo "</form>";
														echo "</td>";
														echo "</tr>";										
												}
										}

								}
								echo "</table>";
								echo "</div>";
						}

				}
			
				// if logged in make logoff button
				echo "<form method='post' action='admin.php'>";
				echo "  <input type='hidden' name='cmd' value='LOGOFF'>";
				echo "  <button>logoff</button>";		
				echo "</form>";			
		}
			
?>
		
</body>

</html>