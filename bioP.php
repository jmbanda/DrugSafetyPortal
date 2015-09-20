<?php
	$term = stripslashes(urldecode($_POST['bioQ']));	
	$ch = curl_init();
	$request_headers = array();
	$request_headers[] = 'Authorization: apikey token=cc4e9843-de6c-4234-8ccf-2579c4750bb0';
	curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
	//$test_query=rawurlencode('ritalin adderall');
	$test_query=rawurlencode($term);
	$url = 'http://data.bioontology.org/annotator?ontologies=MESH&text='.$test_query;
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    $results= curl_exec($ch);
    curl_close($ch);	
	$results_drugs=rawurldecode($results); 
	//echo $results_drugs;
	
	//Get the identifiers for each drug found in the string
	$needle = '"annotatedClass":{"@id"';
	$lastPos = 0;
	$positions = array();
	$mesh_drug_names=array();
	$mesh_drug_codes=array();
	
	while (($lastPos = strpos($results_drugs, $needle, $lastPos))!== false) {
		$positions[] = $lastPos;
		$lastPos = $lastPos + strlen($needle);
	}
	echo "<html>";
	echo "<body>";
	// Displays 3 and 10
	foreach ($positions as $value) {
		//echo $value+23+2;
		//echo "<h3>".substr($results_drugs, $value+23+2 + 42, 7) . "</h3>";   //49
		$mesh_drug_codes[]=substr($results_drugs, $value+23+2 + 42, 7);
		$temp=file_get_contents('http://id.nlm.nih.gov/mesh/'.substr($results_drugs, $value+23+2 + 42, 7).'.json');
		//Get the 

		$jsonIterator = new RecursiveIteratorIterator(
			new RecursiveArrayIterator(json_decode($temp, TRUE)),
			RecursiveIteratorIterator::SELF_FIRST);
		
		foreach ($jsonIterator as $key => $val) {
			if(is_array($val)) {
				//echo "$key:<br>";
				$key_m=$key;
			} else {
				//echo "$key => $val<br>";
				if ($key_m == 'label' && $key=='@value') {
					$mesh_drug_names[] = $val;
					//echo '<h1>'.$val.'</h1>';
				}
			}
		}
		

	}
	//var_dump($mesh_drug_names);
	$cnt=0;
	foreach ($mesh_drug_codes as $mesh_item) {
		echo "<a href='http://cu.mesh.bio2rdf.org/describe/?url=http%3A%2F%2Fbio2rdf.org%2Fmesh%3A".$mesh_item."'>".$mesh_drug_names[$cnt]."</a> <br>";
		
		echo '<div id="drug_interactions"> ';
		
		
		$cnt++;
	}
	
	echo "<br>";
	//var_dump($mesh_drug_codes);
	echo "</body>";
	echo "</html>";
	
	
	//Go to bio2RDF info
// Overkill data 	//http://cu.mesh.bio2rdf.org/sparql?query=define%20sql%3Adescribe-mode%20%22CBD%22%20%20DESCRIBE%20%3Chttp%3A%2F%2Fbio2rdf.org%2Fmesh%3AD018377%3E&output=application%2Frdf%2Bjson

//Link to bio2rdf faceted browser
//http://cu.mesh.bio2rdf.org/describe/?url=http%3A%2F%2Fbio2rdf.org%2Fmesh%3AD018377
?>