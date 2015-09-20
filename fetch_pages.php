<?php
//continue only if $_POST is set and it is a Ajax request
//if(isset($_POST) && isset($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest'){
session_start();	
include("config.php");  //include config file
if(isset($_POST["page"])){
	$page_number = filter_var($_POST["page"], FILTER_SANITIZE_NUMBER_INT, FILTER_FLAG_STRIP_HIGH);
	if(!is_numeric($page_number)){die('Invalid page number!');} //incase of invalid page number
}else{
	$page_number = 1;
}

//get current starting point of records
$position = (($page_number-1) * $item_per_page);

//Limit our results within a specified range. 
$results = mysqli_query($connecDB, 'select *, (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES) as score from ddi WHERE (drug1mesh = "'.$_SESSION['drug'].'" OR drug2mesh = "'.$_SESSION['drug'].'") AND (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES)>0 order by score DESC LIMIT '.$position.','.$item_per_page);

//output results from database
echo '<ul class="page_result">';
while($row = mysqli_fetch_array($results))
{
	if ($row["EHR"]==1) { 
		$ehr='yes';
	} else {
		$ehr='no';
	}
	if ($row["FAERS"]==1) { 
		$faers='yes';
	} else {
		$faers='no';
	}
	if ($row["MEDLINE"]==1) { 
		$medline='yes';
	} else {
		$medline='no';
	}
	if ($row["INDI"]==1) { 
		$indi='yes';
	} else {
		$indi='no';
	}		
	if ($row["VILAR"]==1) { 
		$vilar='yes';
	} else {
		$vilar='no';
	}	
	if ($row["TWOSIDES"]==1) { 
		$twosides='yes';
	} else {
		$twosides='no';
	}		
	echo '<li id="item_'.$row["ddi_id"].'">'.$row["drug1name"].' interacts with '.$row["drug2name"].' can cause '.$row["eventname"].' found in: EHR Data: '.$ehr,', MEDLINE: '.$medline,', TWOSIDES: '.$twosides,', FAERS: '.$faers,', VILAR: '.$vilar,', INDI: '.$indi.' feasibility score:'.$row['score'].'</li>';
}
echo '</ul>';
?>