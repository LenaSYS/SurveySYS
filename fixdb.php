<html>
	<body>
		<pre>
<?php

  $log_db = new PDO('sqlite:./surveydata.db');

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
?>
</pre>
	</body>
</html>