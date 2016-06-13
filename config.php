<?php
$db_host = "localhost"; 
$db_username     = "ds_portal"; 
$db_password = "ds_portal"; 
$db_name = "drugsafetyportal"; 

$item_per_page 		= 10; //item to display per page

$connecDB = new mysqli($db_host, $db_username, $db_password,$db_name); //connect to MySql
//Output any connection error
if ($mysqli_conn->connect_error) {
    die('Error : ('. $mysqli_conn->connect_errno .') '. $mysqli_conn->connect_error);
}

//$conPP = mysql_connect($mysql_hostname, $mysql_user, $mysql_password) or die("Opps error! occurred");
//mysql_select_db($mysql_database, $conPP) or die("Opps error! occurred");
?>