<?php
include('config.php');
session_start();
//$per_page = 15;
$_SESSION['drug']=$_GET['drug']; 

//getting number of rows and calculating no of pages
//$results = $mysqli_conn->query('select count(*) from ddi WHERE (drug1mesh = "'.$_GET['drug'].'" OR drug2mesh = "'.$_GET['drug'].'") AND (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES)>0');
$results = mysqli_query($connecDB,'select count(*) from ddi WHERE (drug1mesh = "'.$_GET['drug'].'" OR drug2mesh = "'.$_GET['drug'].'") AND (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES)>0');
$get_total_rows = mysqli_fetch_array($results); //total records

//break total records into pages
$pages = ceil($get_total_rows[0]/$item_per_page);	

?>
<!DOCTYPE html>
<html>
<head>
<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
<title>Results pagination</title>
<script type="text/javascript" src="js/jquery-1.11.2.min.js"></script>
<script type="text/javascript" src="js/jquery.bootpag.min.js"></script>
<script type="text/javascript">
$(document).ready(function() {
	$("#results").load("fetch_pages.php");  //initial page number to load
	$(".pagination").bootpag({
	   total: <?php echo $pages; ?>,
	   page: 1,
	   maxVisible: 15 
	}).on("page", function(e, num){
		e.preventDefault();
		$("#results").prepend('<div class="loading-indication"><img src="ajax-loader.gif" /> Loading...</div>');
		$("#results").load("fetch_pages.php", {'page':num});
	});

});
</script>
<link href="css/style.css" rel="stylesheet" type="text/css">
</head>
<body>
<h2> NOTE: These results are found in drug safety / pharmacovigilance research studies that aim to predict statistically plausible drug-drug interactions and their effects. These results are highly experimental and have not been validaded through experimentation or by experts</h2>
<h2> Total number of possible interactions found: <?php echo floor($get_total_rows[0]/2); ?> </h2>
<div id="results"></div>
<div class="pagination"></div>
</body>
</html>