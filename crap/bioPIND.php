<?php
	$term = stripslashes(urldecode($_POST['bioCondition']));	
	$test_query=rawurlencode($term);
//http://data.bioontology.org/annotator?apikey=8b5b7825-538d-40e0-9e9e-5ab9274a9aeb&text=heart%20attack&ontologies=SNOMEDCT&longest_only=true&exclude_numbers=false&whole_word_only=false&exclude_synonyms=false
	$resultOTHR = file_get_contents('http://data.bioontology.org/annotator?apikey=cc4e9843-de6c-4234-8ccf-2579c4750bb0&text='.$test_query.'&ontologies=SNOMEDCT&longest_only=true&exclude_numbers=false&whole_word_only=false&exclude_synonyms=false');
	//echo $resultOTHR;
			$jsonIterator = new RecursiveIteratorIterator(
			new RecursiveArrayIterator(json_decode(($resultOTHR), TRUE)),
			RecursiveIteratorIterator::SELF_FIRST);
		
		foreach ($jsonIterator as $key => $val) {
			if(is_array($val)) {
				//echo "$key:<br>";
				$key_m=$key;
			} else {
				//echo "$key => $val<br>";
				if ($key_m == 'links' && $key=='self') {
						$ch = curl_init();
						$request_headers = array();
						$request_headers[] = 'Authorization: apikey token=cc4e9843-de6c-4234-8ccf-2579c4750bb0';
						curl_setopt($ch, CURLOPT_HTTPHEADER, $request_headers);
						$url = trim($val);
						curl_setopt($ch, CURLOPT_URL, $url);
						curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
						$results= curl_exec($ch);
						curl_close($ch);	
						$result2=rawurldecode($results);
						$jsonIterator2 = new RecursiveIteratorIterator(
						new RecursiveArrayIterator(json_decode(rawurldecode($result2), TRUE)),
						RecursiveIteratorIterator::SELF_FIRST);	
						foreach ($jsonIterator2 as $key2 => $val2) {
							if(is_array($val2)) {
								//echo "$key2:<br>";
								$key_m2=$key2;
							} else {
								//echo "$key2 => $val2<br>";
								if ($key2=='prefLabel') {
									$name_var[]= $val2;
								}
								if ($key_m2=='cui' && $key2=='0') {
									$cui_var=$val2;
								}
							}
						}
				}
			}
		}
		print "Drug Name: ".$name_var[0].' , CUI: '.$cui_var;
?>