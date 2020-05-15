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
	</script>

	</head>
	
	<body>
		
<?php			

	date_default_timezone_set('Europe/Stockholm');

	//------------------------------------------------------------------------------------------------
	// stats_standard_deviation
	//------------------------------------------------------------------------------------------------
	/**
	* This user-land implementation follows the implementation quite strictly;
	* it does not attempt to improve the code or algorithm in any way. It will
	* raise a warning if you have fewer than 2 values in your array, just like
	* the extension does (although as an E_USER_WARNING, not E_WARNING).
	*
	* @param array $a
	* @param bool $sample [optional] Defaults to false
	* @return float|bool The standard deviation or false on error.
	*/
	function stats_standard_deviation(array $a, $sample = false) {
			$n = count($a);
			if ($n === 0) {
					trigger_error("The array has zero elements", E_USER_WARNING);
					return false;
			}
			if ($sample && $n === 1) {
					trigger_error("The array has only 1 element", E_USER_WARNING);
					return false;
			}
			$mean = array_sum($a) / $n;
			$carry = 0.0;
			foreach ($a as $val) {
					$d = ((double) $val) - $mean;
					$carry += $d * $d;
			};
			if ($sample) {
				 --$n;
			}
			return sqrt($carry / $n);
	}

	//------------------------------------------------------------------------------------------------
	// getOP
	//------------------------------------------------------------------------------------------------

	function getOP($name)
	{
			if(isset($_POST[$name]))	return $_POST[$name];
			else return "UNK";
	}
		
	//------------------------------------------------------------------------------------------------

	$kumho=425;
		
	$description=getOP('description');
	$labelA=getOP('labelA');
	$labelB=getOP('labelB');		
  $labelC=getOP('labelC');
	$type=getOP('type');
	$id=getOP('id');
		
	$swapid=getOP('SwapId');
	$swapno=getOP('SwapNo');		
	$swapoutid=getOP('SwapOutId');
	$swapoutno=getOP('SwapOutNo');
	$scatter=getOP('scatter');
	
	//print_r($_POST);
		
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
	$sql = 'CREATE TABLE IF NOT EXISTS response(id INTEGER PRIMARY KEY,resphash VARCHAR(32),hash VARCHAR(32),questno INTEGER, itemid INTEGER, val TEXT, useragent TEXT, userhash varchar(32));';		
	$log_db->exec($sql);	

	$log_db->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_WARNING);		
		
	if($cmd=="LOGOFF"){
				session_unset();
				session_destroy();
				$_SESSION=Array();
				$hash="KUMHO";
	}

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
			$rows = $query->fetchAll(PDO::FETCH_ASSOC);	
			foreach($rows as $row){
					$datarow=$row;
			}
	}
		
	if(sizeof($datarow)>0){
		
			echo "Survey: ".$hash."<br>";
		
			$_SESSION['hash']=$hash;
      $_SESSION['admincode']=$admincode;
      $_SESSION['surveyname']=$datarow["name"];
      $_SESSION['surveydescription']=$datarow["description"];

			echo "<div id='admincode'>\n";
			echo "<form method='POST' name='editSurvey' action='editSurvey.php' >\n";
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
					$query = $log_db->prepare('INSERT INTO item(hash,description,type,labelA,labelB,labelC,questno) VALUES (:hash,:description,:type,:labelA,:labelB,:labelC,IFNULL((select max(questno) from item where hash=:hasho)+1,1))');					
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
			}else if($cmd=="SWAP"){
					$query = $log_db->prepare('UPDATE item set questno=:questno where id=:id;');					
					$query->bindParam(':id', $swapid);				
					$query->bindParam(':questno', $swapoutno);
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}
					$query = $log_db->prepare('UPDATE item set questno=:questno where id=:id;');					
					$query->bindParam(':id', $swapoutid);				
					$query->bindParam(':questno', $swapno);
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}				
			}else if($cmd=="DEL"){
					echo "DELETING!".$id;
					$query = $log_db->prepare('DELETE FROM item WHERE id=:id;');					
					$query->bindParam(':id', $id);				
					if (!$query->execute()) {
							$error = $query->errorInfo();
							$debug = "Error updating database: " . $error[2];
					}					
			}else if($cmd=="UPD"){
          echo "UPDATING".$id;
          $updateArr=array();
					if($type!="UNK"){
              //echo "TYPE";
              array_push($updateArr,array("column"=>"type","value"=>$type));
					}else if($labelA!="UNK"){
              //echo "LABL";	
              array_push($updateArr,array("column"=>"labelA","value"=>$labelA));
              array_push($updateArr,array("column"=>"labelB","value"=>$labelB));
              array_push($updateArr,array("column"=>"labelC","value"=>$labelC));
					}else if($description!="UNK"){
              //echo "DESC";					
              array_push($updateArr,array("column"=>"description","value"=>$description));
          }

          foreach($updateArr as $update){
              $sql='UPDATE item SET '.$update["column"].'=:value WHERE id=:id;';
              $query = $log_db->prepare($sql);					
              $query->bindParam(':id', $id);
              $query->bindParam(':value', $update["value"]);
              if (!$query->execute()) {
                  $error = $query->errorInfo();
                  $debug = "Error updating ".$update["column"].": \n\n\n" . $error[2];
              }					  
          }          

			}else if($cmd=="UPDDESC"){
          echo "UPDATING DESCRIPTION FOR ".$id;
          
          $sql='UPDATE survey SET description=:desc WHERE hash=:hash;';
          $query = $log_db->prepare($sql);					
          $query->bindParam(':hash', $hash);
          $query->bindParam(':desc', $description);
          if (!$query->execute()) {
              $error = $query->errorInfo();
              $debug = "Error updating ".$update["column"].": \n\n\n" . $error[2];
          }	          
      }else if($cmd=="EXPO"||$cmd=="EXPOSVG"){
				
					$csv="";
					$svgarr=Array();
					$userarr=Array();
				
					// Retrieve list of all user hashes for any response -- no resubmissions count
					$query=$log_db->prepare('SELECT distinct(userhash) FROM response where hash=:hash');
					$query->bindParam(':hash', $hash);
					if (!$query->execute()) {
							$error = $log_db->errorInfo();
							print_r($error);
					}else{
							
							$rows = $query->fetchAll();	
							foreach($rows as $row){
									array_push($userarr,$row['userhash']);
							}
					}
				
					// Retrieve full database and swizzle into associative array for each day
					$query=$log_db->prepare('SELECT * FROM item where hash=:hash order by questno');
					$query->bindParam(':hash', $hash);
					if (!$query->execute()) {
							$error = $log_db->errorInfo();
							print_r($error);
					}else{
							
							$labels=explode(",","A,B,C,D,E,F,G,H,I,J,K,L,M,N,O,P,Q,R,S,T,U,V,X,Y,Z,AA,AB,AC,AD,AE,AF,AG,AH,AI,AJ");
							$i=1;
							$show=false;
							$rows = $query->fetchAll();	
							foreach($rows as $row){
																
									// Any text-based response independent of the number of respondents i.e. same user may resubmit
									$cquery=$log_db->prepare('SELECT * FROM response where hash=:hash and itemid=:itemid;');
									$cquery->bindParam(':hash', $hash);
									$cquery->bindParam(':itemid', $row['id']);
									if (!$cquery->execute()) {
											$error = $log_db->errorInfo();
											print_r($error);
									}else{
											$crows = $cquery->fetchAll();	
											
											// Make headings
											if($row['type']==2&&$show==false){
												
													$show=true;
												
													$csv.="rowno";
													$csv.=",questno";
													$csv.=",description";

													$j=0;
													foreach($userarr as $user){
															$j++;
															$csv.=",resp".$j;
													}

													$csv.=",min";
													$csv.=",max";
													$csv.=",avg";
												
													$csv.="\\n";											
											}

											$csv.=$i.",";
											$csv.=$row['questno'].",";
											$csv.=$row['description'];
										
											$max=0;
											$min=10000;
											$avg=0;
											$sca="";
										
											$statitems=Array();
											
											// Iterate over all responses of this question to collect max and min
											foreach($crows as $crow){
													if($max<floatval($crow['val'])) $max=floatval($crow['val']);
													if($min>floatval($crow['val'])) $min=floatval($crow['val']);
													$avg+=(floatval($crow['val'])/count($crows));
													$sca.="<circle cx='".((($i-1)*50)+25)."' cy='".($kumho-(floatval($crow['val'])*50))."' r='3' fill='blue' opacity='0.1' />";
													array_push($statitems,floatval($crow['val']));
											}
										
											// Iterate over all users to perform per user computation - only last response in list is kept
											foreach($userarr as $user){
													$theval="";
													foreach($crows as $crow){
															if($crow['userhash']==$user) $theval=$crow['val'];
													}
													$csv.=",".$theval;
											}
														
											$stdev=0;
											if(count($crows)>1){
													$stdev=stats_standard_deviation($statitems);
											}

											$firstcol=3;
											$lastcol=2+count($userarr);

											$i++;

											// If it is a number add the max min average columns
											if(($row['type']==2)&&(count($userarr)>0)){
													
													$theitem=Array();
													array_push($theitem,$row['description']);
													array_push($theitem,$row['questno']);
													array_push($theitem,$avg);
													array_push($theitem,$min);
													array_push($theitem,$max);
													array_push($theitem,$stdev);
													array_push($theitem,$sca);												  
												
													array_push($svgarr,$theitem);												
													
													$colS=$labels[$firstcol];
													$colE=$labels[$lastcol];
													$csv.= ","."=MIN(".$colS.$i.":".$colE.$i.")";												
													$csv.= ","."=MAX(".$colS.$i.":".$colE.$i.")";																								
													$csv.= ","."=AVERAGE(".$colS.$i.":".$colE.$i.")";
											}

									
									}
									
									$csv.="\\n";
							}
					}					
        
          $csv=str_replace("'","",$csv);
					if($cmd=="EXPO"){
              echo "<script>";
							echo "var csvContent=`".$csv."`;";
							echo "var encodedUri = 'data:text/csv;charset=utf-8,'+encodeURI(csvContent);";
							echo "var link = document.createElement('a');";
							echo "link.setAttribute('href', encodedUri);";
							echo "link.setAttribute('download', 'my_data.csv');";
							echo "document.body.appendChild(link);";
							echo "link.click();";
							echo "</script>";
					}else if($cmd=="EXPOSVG"){
							$cnt=count($svgarr);
							$chartwidth=($cnt*50)+100;
							$svg="<svg viewBox='0 0 ".$chartwidth." 600' xmlns='http://www.w3.org/2000/svg'>";
							
							for($i=0;$i<$cnt;$i++){
									if(($i%2)==0){
											$svg.="<rect x='".(($i*50)+50)."' y='60' width='50' height='340' fill='rgb(230,230,230)' />";
									}else{
											$svg.="<rect x='".(($i*50)+50)."' y='60' width='50' height='340' fill='rgb(245,245,245)' />";
									}
							}
							
							for($i=0;$i<7;$i++){
									$svg.="<line x1='45' y1='".(($i*50)+75)."' x2='55' y2='".(($i*50)+75)."' stroke='rgb(0,0,0)' strokewidth='2' />";							
									$svg.="<text x='35' y='".(($i*50)+80)."' fill='rgb(0,0,0)' fontfamily='Arial' font-size='12' text-anchor='left' dominant-baseline='central' >".(7-$i)."</text>";							
							}

							$svg.="<line x1='50' y1='50' x2='50' y2='400' stroke='rgb(0,0,0)' strokewidth='2' />";
							$svg.="<line x1='50' y1='400' x2='".$chartwidth."' y2='400' stroke='rgb(0,0,0)' strokewidth='2' />";

							$pnt="";
							$iv="";
							$stv="";
						
							$circ="";
						
							for($i=0;$i<$cnt;$i++){
										$val=floatval($svgarr[$i][2]);
										$minval=floatval($svgarr[$i][3]);
										$maxval=floatval($svgarr[$i][4]);
										$stdev=floatval($svgarr[$i][5]);
										if($i==0) $pnt.=(($i*50)+50).",".($kumho-($val*50));
										if($i==0) $iv.=(($i*50)+50).",".($kumho-($minval*50));
										if($i==0) $stv.=(($i*50)+50).",".($kumho-(($val*50)-($stdev*50)));										

										$stdev=floatval($svgarr[$i][5]);

										if($scatter=='on') $circ.=$svgarr[$i][6];
										
										$circ.="<circle cx='".(($i*50)+75)."' cy='".($kumho-($val*50))."' r='4' fill='green'/>";
										$circ.="<text x='".(($i*50)+75)."' y='".($kumho-($val*50)-10)."' fill='black' text-anchor='middle' >".round($val,1)."</text>";
								
										$pnt.=",".(($i*50)+75).",".($kumho-($val*50));
										$iv.=",".(($i*50)+75).",".($kumho-($minval*50));
										$stv.=",".(($i*50)+75).",".($kumho-(($val*50)-($stdev*50)));
							}
							if($cnt>0){
										$pnt.=",".(($i*50)+50).",".($kumho-($val*50));								
										$iv.=",".(($i*50)+50).",".($kumho-($minval*50));
										$iv.=",".(($i*50)+50).",".($kumho-($maxval*50));
										$stv.=",".(($i*50)+50).",".($kumho-(($val*50)-($stdev*50)));								
										$stv.=",".(($i*50)+50).",".($kumho-(($val*50)+($stdev*50)));									
							}
							$i--;
							for(;$i>=0;$i--){
										$maxval=floatval($svgarr[$i][4]);
										$val=floatval($svgarr[$i][2]);
										$stdev=floatval($svgarr[$i][5]);	
										$iv.=",".(($i*50)+75).",".($kumho-($maxval*50));
										$stv.=",".(($i*50)+75).",".($kumho-(($val*50)+($stdev*50)));
							}
							if($cnt>0){
										$maxval=floatval($svgarr[0][4]);
										$val=floatval($svgarr[0][2]);
										$stdev=floatval($svgarr[0][5]);
										$iv.=",".(($i*50)+100).",".($kumho-($maxval*50));
										$stv.=",".(($i*50)+100).",".($kumho-(($val*50)+($stdev*50)));								
							}
						
							$svg.="<polyline points='".$iv."' stroke='none' fill='lightblue' stroke-width='3' opacity='0.4' />";							
							$svg.="<polyline points='".$stv."' stroke='none' fill='rgb(32,128,32)' stroke-width='3' opacity='0.1' />";																																	 
							$svg.="<polyline points='".$pnt."' stroke='green' fill='none' stroke-width='3' />";
						
							$svg.=$circ;	
						
							for($i=0;$i<$cnt;$i++){
								$textrows = explode("\n", wordwrap($svgarr[$i][0], 60));
								for($j=0;$j<count($textrows);$j++) {
									$svg.="<text x='".(($i*50)+75)."' y='".(($j*10)+410)."'  fill='rgb(0,0,0)' transform='rotate(45 ".(($i*50)+75)." 410)' inline-size='200px' fontfamily='Arial' font-size='10' text-anchor='left' dominant-baseline='central' >".$textrows[$j]."</text>";
								}
							}
						
							$svg.="<polyline points='47,50,53,50,50,42' stroke='none' fill='black' stroke-width='3' />";
							$svg.="<polyline points='".$chartwidth.",397,".$chartwidth.",403,".($chartwidth+8).",400' stroke='none' fill='black' stroke-width='3' />";						
						
							$svg.="</svg>";

							echo "<script>";
							echo "var svgContent=`".$svg."`;";
							echo "var encodedUri = 'data:text/svg;charset=utf-8,'+encodeURI(svgContent);";
							echo "var link = document.createElement('a');";
							echo "link.setAttribute('href', encodedUri);";
							echo "link.setAttribute('download', 'my_data.svg');";
							echo "document.body.appendChild(link);";
							echo "link.click();";
							echo "</script>";
					
					}
				
			}
		
			// Retrieve full database and swizzle into associative array for each day
			$query=$log_db->prepare('SELECT * FROM item where hash=:hash order by questno;');
			$query->bindParam(':hash', $hash);
			if (!$query->execute()) {
					$error = $log_db->errorInfo();
					print_r($error);
			}else{
				
					// Export!
					echo "<form method='post' action='editSurvey.php' >";
					echo "<input type='hidden' name='hash' value='".$hash."'>";
					echo "<input type='hidden' name='CMD' value='EXPO'>";
					echo "<input type='submit' value='Export csv' >\n";
					echo "</form>";

					// Export!
					echo "<form method='post' action='editSurvey.php' >";
					echo "<input type='hidden' name='hash' value='".$hash."'>";
					echo "<input type='hidden' name='CMD' value='EXPOSVG'>";
					echo "<input type='submit' value='Export svg' >\n";
					echo "Scatter: <input type='checkbox' name='scatter' >";
					echo "</form>";
				
					// Logoff
					echo "<form method='post' action='editSurvey.php' >";
					echo "<input type='hidden' name='CMD' value='LOGOFF'>";
					echo "<input type='submit' value='Logoff' >\n";
					echo "</form>";				
        
          echo "<form method='post' action='editSurvey.php' >";
          echo "<input type='hidden' name='hash' value='".$hash."'>";
          echo "<input type='hidden' name='id' value='".$id."'>";
          echo "<input type='hidden' name='CMD' value='UPDDESC'>";
          echo "<input type='text' name='description' value='".$datarow['description']."'>";
					echo "<input type='submit' value='Update Description' >\n";
					echo "</form>";
          echo "<table>";
          
					echo "<tr><th>Prio</th><th>Type</th><th>Labels (Left Right Center)</th><th>Description/Question</th></tr>";

					$lastrow='UNK';
					$lastno='UNK';
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
								echo "<input type='hidden' name='CMD' value='UPD'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";

								// Labels
                echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
                if($row['type']==1){
                    echo "<input type='text' name='labelA' value='".$row['labelA']."' placeholder='Before link'>";
                    echo "<input type='text' name='labelB' value='".$row['labelB']."' placeholder='After link'>";
                    echo "<input type='hidden' name='labelC' value='".$row['labelC']."' >";						
              }else if($row['type']==2){
                    echo "<input type='text' name='labelA' value='".$row['labelA']."' placeholder='Left'>";
                    echo "<input type='text' name='labelB' value='".$row['labelB']."' placeholder='Right'>";
                    echo "<input type='text' name='labelC' value='".$row['labelC']."' placeholder='Center'>";						  
                }else if($row['type']==3){
                    echo "<input type='text' name='labelA' value='".$row['labelA']."' placeholder='Label'>";
                    echo "<input type='hidden' name='labelB' value='".$row['labelB']."'>";
                    echo "<input type='hidden' name='labelC' value='".$row['labelC']."'>";						  
                }else{
                  echo "Unknown type: ".$row['type'];
                }                    
								echo "<input type='hidden' name='CMD' value='UPD'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";
						
								// Description
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<textarea name='description' >".$row['description']."</textarea>";				
								echo "<input type='hidden' name='CMD' value='UPD'>";
								echo "<input type='submit' value='Save' >\n";
								echo "</form></td>";								

								// Swap
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<input type='hidden' name='CMD' value='SWAP'>";
								if($lastrow!="UNK"){
										echo "<input type='submit' value='Swap' >\n";
										echo "<input type='hidden' name='SwapId' value='".$row['id']."'>";
										echo "<input type='hidden' name='SwapNo' value='".$row['questno']."'>";
										echo "<input type='hidden' name='SwapOutId' value='".$lastrow."'>";
										echo "<input type='hidden' name='SwapOutNo' value='".$lastno."'>";
									
								}
								echo "</form></td>";

								// Del
								echo "<td style='border:1px solid red;border-radius:4px;'><form method='post' action='editSurvey.php' ><input type='hidden' name='id' value='".$row['id']."'>";
								echo "<input type='hidden' name='CMD' value='DEL'>";
								echo "<input type='submit' value='Del' >\n";
								echo "</form></td>";
						
								echo "</tr>";
						
								$lastrow=$row['id'];
								$lastno=$row['questno'];
					}
					echo "</table>";

					echo "<h3>Preview</h3>";	
				
					// Preview
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
									echo "<tr><td colspan='2'>".$row['labelA']." <a href='".$row['description']."' target='_blank'>".$row['description']."</a> ".$row['labelB']."</td></tr>";
																	
									echo "</table></td>";
                  echo "</tr>";
                }
					}
          echo "</tbody>";
          echo "<tfoot>";
          echo "<tr>";
          echo "<th>";
          $actual_link = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://$_SERVER[HTTP_HOST]$_SERVER[REQUEST_URI]";
          $actual_link=explode("?",$actual_link)[0];
          $actual_link=str_replace("editSurvey.php","doSurvey.php",$actual_link);
          echo "Survey URL: <a href='".$actual_link."?hash=".$hash."'>".$actual_link."?hash=".$hash."</a>";
          echo "</th>";
          echo "</tr>";
          echo "</tfoot>";
          echo "</table>";
			}
		
			
			
			
			
	}else{
		
			session_unset();
			session_destroy();
			$_SESSION=Array();
		
			// Make survvey administration form 
			echo "<div id='admincode'>\n";
			echo "<form method='POST' name='editSurvey' action='editSurvey.php' >\n";
			echo "<input type='hidden' name='CMD' value='EDIT' >\n";
			echo "<table>\n";
			echo "<tr><td>Hash:</td><td><input type='text' placeholder='Enter Hash' name='hash' ></td></tr>\n";
			echo "<tr><td>Code:</td><td><input type='text' placeholder='Admin Code' name='admincode' ></td></tr>\n";		
			echo "</table>\n";
			echo "<input type='submit' value='Edit Survey' >\n";
			echo "</form>\n";
			echo "</div>\n";
	}
		
?>
		
</body>

</html>
