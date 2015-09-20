<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>DrugSafety Portal</title>
</head>
<body>
<div id="wrap">
<form id="create" action="PubMed.php" method="POST" accept-charset="utf-8">
	<p><label for="search_term">Search term: </label> <input type="text" name="term" value="Ritalin" id="search_term"> <button id="submit-button">Submit PubMed</button></p>
</form>
<form id="create" action="bioP.php" method="POST" accept-charset="utf-8">
	<p><label for="search_term">Search term: </label> <input type="text" name="bioQ" value="Ritalin and Adderall" id="search_term"> <button id="submit-button">Submit BioPortal</button></p>
</form>
<form id="create" action="bioPIND.php" method="POST" accept-charset="utf-8">
	<p><label for="search_term">Search term: </label> <input type="text" name="bioCondition" value="Heart Attack" id="search_term"> <button id="submit-button">Submit BioPortal Condition</button></p>
</form>
<form id="create" action="bioP_Medline.php" method="POST" accept-charset="utf-8">
	<p><label for="search_term">Search term: </label> <input type="text" name="bioMedline" value="Ritalin and Adderall" id="search_term"> <button id="submit-button">Submit Drugs and Medline</button></p>
</form>
</div>
</body>
</html>