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
		
?>
		
</body>

</html>