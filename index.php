<!DOCTYPE html>
<html lang="en">
<head>
	<meta http-equiv="Content-type" content="text/html; charset=utf-8">
	<title>DrugSafety Portal</title>
</head>
<body>
<div id="wrap">
<form id="create" action="PubMed.php" method="POST" accept-charset="utf-8">
	<p><label for="search_term">Search term: </label> <input type="text" name="term" value="<?php echo isset($_POST['term'])?stripslashes($_POST['term']):'Ritalin'; ?>" id="search_term"> <button id="submit-button">Submit PubMed</button></p>
</form>
</div>
</body>
</html>