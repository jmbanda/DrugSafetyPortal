<?php
include('config.php');
session_start();
$per_page = 15; 
if($_GET)
{
 $page=$_GET['page'];
}
//getting table contents
$start = ($page-1)*$per_page; 
$sql   = 'select *, (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES) as score from ddi WHERE (drug1mesh = "'.$_SESSION['drug'].'" OR drug2mesh = "'.$_SESSION['drug'].'") AND (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES)>0 order by score DESC limit '.$start.','.$per_page;
//echo '<br>'.$sql.'</br>';
$rsd   = mysql_query($sql);
?>

<table id="tbl">
   <th><b>Drug</b></th>
   <th><b>Drug</b></th>
   <th><b>Event</b></th>
   <th><b>Source: EHR</b></th>
   <th><b>Source: FAERS</b></th>  
   <th><b>Source: TWOSIDES</b></th>
   <th><b>Source: MEDLINE</b></th>
   <th><b>Source: VILAR</b></th>      
   <th><b>Source: INDI</b></th>  
   <th><b>SCORE</b></th>    
   <?php
   while($row = mysql_fetch_array($rsd))
   {
    $drug1    = $row['drug1name'];
    $drug2 = $row['drug2name'];
    $event = $row['eventname'];
    $ehr = $row['EHR'];
	$faers = $row['FAERS'];
	$twosides = $row['TWOSIDES'];
	$medline = $row['MEDLINE'];
	$vilar = $row['VILAR'];
	$indi = $row['INDI'];
	$score = $row['score'];
   ?>
   <tr>
      <td><?php echo $drug1; ?></td>
      <td><?php echo $drug2; ?></td>
      <td><?php echo $event; ?></td>
      <td><?php echo $ehr; ?></td>
      <td><?php echo $faers; ?></td>
      <td><?php echo $twosides; ?></td>
      <td><?php echo $medline; ?></td>
      <td><?php echo $vilar; ?></td>
      <td><?php echo $indi; ?></td>    
      <td><?php echo $score; ?></td>                        
  </tr>
  <?php
  } //End while
  ?>
</table>