<?php

include 'dbstuff.php';
		//DB STUFF
		// Create connection
		$conn = new mysqli($servername, $username, $password, $dbname);
		// Check connection
		if ($conn->connect_error) {
			die("Connection failed: " . $conn->connect_error);
		}		
		// PDO migration
		$db_pdo = new PDO('mysql:host=localhost;dbname=drugsafetyportal;charset=utf8mb4', 'root', '');
		//DB STUFF - done		

		//MEDLINE Querying stuff
		$proxy_name = '';
		$proxy_port = '';
		$proxy_username = '';
		$proxy_password = '';
		$curl_site_url = '';
		function query_pmid($pmid, $compact = false)
		{
			$XML = pubmed_efetch($pmid);
			return parse($XML, $compact);
		}
		
		// Returns an XML object
		function pubmed_efetch($pmid)
		{
			// Setup the URL for efetch
			$params = array(
				'db'		=> 'pubmed',
				'retmode'	=> 'xml',
				'retmax'	=> 25,
				'id'		=> (string) $pmid
			);
			$efetch = 'http://eutils.ncbi.nlm.nih.gov/entrez/eutils/efetch.fcgi?';
			$q = array();
			foreach ($params as $key => $value) { $q[] = $key . '=' . $value; }
			$httpquery = implode('&',$q);
			$url = $efetch . $httpquery;
			$XML = proxy_simplexml_load_file($url);
			return $XML;
		}
	
		function parse($xml, $compact = false)
		{
			$data = array();
			foreach ($xml->PubmedArticle as $art) {
				if ($compact) {
					// Compact
					$data[] = array(
						'pmid'			=> (string) $art->MedlineCitation->PMID,
						'volume'		=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->Volume,
						'issue'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->Issue,
						'year'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year,
						'month'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->PubDate->Month,
						'journal'		=> (string) $art->MedlineCitation->Article->Journal->Title,
						'journalabbrev'	=> (string) $art->MedlineCitation->Article->Journal->ISOAbbreviation,
						'title'			=> (string) $art->MedlineCitation->Article->ArticleTitle,
					);
				} else {
					// Full metadata
	
					// Authors array contains concatendated LAST NAME + INITIALS
					$authors = array();
					if (isset($art->MedlineCitation->Article->AuthorList->Author)) {
						try {
							foreach ($art->MedlineCitation->Article->AuthorList->Author as $k => $a) {
								$authors[] = (string)$a->LastName .' '. (string)$a->Initials;
							}
						} catch (Exception $e) {
							$a = $art->MedlineCitation->Article->AuthorList->Author;
							$authors[] = (string)$a->LastName .' '. (string)$a->Initials;
						}
					}
	
					// Keywords array
					$keywords = array();
					if (isset($art->MedlineCitation->MeshHeadingList->MeshHeading)) {
						foreach ($art->MedlineCitation->MeshHeadingList->MeshHeading as $k => $m) {
							$keywords[] = (string)$m->DescriptorName;
							if (isset($m->QualifierName)) {
								if (is_array($m->QualifierName)) {
									$keywords = array_merge($keywords,$m->QualifierName);
								} else {
									$keywords[] = (string)$m->QualifierName;
								}
							}
						}
					}
	
					// Article IDs array
					$articleid = array();
					if (isset($art->PubmedData->ArticleIdList)) {
						foreach ($art->PubmedData->ArticleIdList->ArticleId as $id) {
							$articleid[] = $id;
						}
					}
	
	
					$data[] = array(
						'pmid'			=> (string) $art->MedlineCitation->PMID,
						'volume'		=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->Volume,
						'issue'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->Issue,
						'year'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->PubDate->Year,
						'month'			=> (string)$art->MedlineCitation->Article->Journal->JournalIssue->PubDate->Month,
						'pages'			=> (string) $art->MedlineCitation->Article->Pagination->MedlinePgn,
						'issn'			=> (string)$art->MedlineCitation->Article->Journal->ISSN,
						'journal'		=> (string) $art->MedlineCitation->Article->Journal->Title,
						'journalabbrev'	=> (string) $art->MedlineCitation->Article->Journal->ISOAbbreviation,
						'title'			=> (string) $art->MedlineCitation->Article->ArticleTitle,
						'abstract'		=> (string) $art->MedlineCitation->Article->Abstract->AbstractText,
						'affiliation'	=> (string) $art->MedlineCitation->Article->Affiliation,
						'authors'		=> $authors,
						'articleid'		=> implode(',',$articleid),
						'keywords'		=> $keywords
					);
				}
			}
			return $data;
		}
		function proxy_simplexml_load_file($url)
		{		
			$xml_string = '';
			ini_set('user_agent', $_SERVER['HTTP_USER_AGENT']);
			$xml = load_xml_from_url($url);
				#JSTOR hack
				if (empty($xml) && strpos($url, 'jstor') !== false) {
					$xml = new XMLReader();
					$xml->open($url);
			}
			return $xml;
		}
	
		function load_file_from_url($url)
		{
			$curl = curl_init();
			curl_setopt($curl, CURLOPT_URL, $url);
			curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
			curl_setopt($curl, CURLOPT_REFERER, $curl_site_url);
			$str = curl_exec($curl);
			curl_close($curl);
			return $str;
		}
	
		function load_xml_from_url($url)
		{
			return simplexml_load_string(load_file_from_url($url));
		}	
	//END OF MEDLINE STUFF

	// Query Recording
	
	if ($_SERVER['HTTP_CLIENT_IP']!="") {
		$ip = $_SERVER['HTTP_CLIENT_IP'];
	} elseif ($_SERVER['HTTP_X_FORWARDED_FOR']!="") {
		$ip = $_SERVER['HTTP_X_FORWARDED_FOR'];
	}
	else  {
		$ip = $_SERVER['REMOTE_ADDR'];
	}
	
	$stmt = $db_pdo->prepare("INSERT INTO query_hist (user,query) VALUES(:usr,:qry)");
	$stmt->execute(array(':usr' => $ip, ':qry' => urldecode($_POST['bioMedline'])));
	
	
	$term = stripslashes(urldecode($_POST['bioMedline']));	
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
	echo "<head>";
	echo '
	<style type="text/css" media="screen">
		body{font-family:"Helvetica Neue",Helvetica,sans-serif;font-size:14px;line-height:19px;}
		h1 a{color:#A00;}h1 a:hover{text-decoration:none;}
		pre,code{font-family:Monaco,monospace;font-size:12px;}pre{padding:20px;background:#EEE;}
		a{color:#00a;text-decoration:none;}a:hover{color:#000;text-decoration:underline;}
		table{width:100%;}
		th{text-align:left;border-bottom:1px solid #CCC;padding:5px;}
		td{vertical-align:top;padding:5px;}
		#wrap{width:960px;margin:0 auto;}
		input[type=text]{width:300px;font-size:inherit;font-family:inherit;border:1px solid #BBB;padding:5px;}
	</style></head>';
	
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
		echo "Drug name: <a href='http://mesh.bio2rdf.org/describe/?url=http%3A%2F%2Fbio2rdf.org%2Fmesh%3A".$mesh_item."' target='_blank'>".$mesh_drug_names[$cnt]."</a> <br>";
		//Interactions found in the DB
		$fetch_stuff = mysqli_query($conn, "SELECT count(ddi_id)/2 as countTT FROM ddi WHERE (drug1mesh = '".$mesh_item."' OR drug2mesh = '".$mesh_item."') AND (EHR+FAERS+INDI+MEDLINE+VILAR+TWOSIDES)>0"); 
		//$row_res= mysqli_fetch_row($fetch_stuff);		
		
		if($results->num_rows == 0) {
			echo '<h3> No interactions found in research datasets </h3>';
		} else {
		$row_res = mysqli_fetch_row($fetch_stuff);
			echo '<h3> Interactions found in research datasets: '.floor($row_res[0]).', <a href="paginate.php?drug='.$mesh_item.'">check here to see them</a></h3>';
			$ddi=$ddi+1;
		}
		
		//mysql_close($conn);			
		
		echo '<div id="medline_results"> ';
		//Lets look for the medline results for each drug
		$term = $mesh_drug_names[$cnt];
		$ADR_query='"adverse effects"[Subheading] AND "chemically induced"[Subheading] AND "Chemicals and Drugs Category"[Mesh]';
		//$query_string=urlencode('"Chemicals and Drugs Category/adverse effects"[Mesh] AND "chemically induced"[Subheading] AND "Drug interactions"[Mesh]');
		$query_string=$ADR_query. ' AND "'.$term.'"[Mesh]';
		//echo '<p>'.$query_string.'</p>';
		$result = file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmax=5&term='.urlencode($query_string).'&retmode=xml');
		$xml = simplexml_load_string($result);
		//
		$total_hits=$xml->Count;
		if ($total_hits == 0) {
			echo '<p> No results found for '.$term.'<p>';
		} else {		
			$results = array();
				if (isset($xml->IdList->Id) && !empty($xml->IdList->Id)) {
					$ids = array();
					foreach ($xml->IdList->children() as $id) {
						$ids[] = (string)$id;
					}
					$results = query_pmid(implode(',',$ids), $compact);
				}
			$tr_results=$xml->Count;
			if ($tr_results < 5) {
				$recent=$tr_results;
			} else {
				$recent=5;
			}
			
			echo "<p>Search results for <strong>".urldecode($query_string)."</strong> (".$tr_results." results, showing the most recent ".$recent.")</p>";			
			//echo "<p>Search results for <strong>".urldecode($query_string)."</strong> (".$xml->Count." results, showing the most recent 5)</p>";
			echo '<table border="0" cellspacing="0" cellpadding="0">';
			echo "   <tr>";
			echo "      <th>PMID</th>";
			echo "      <th>Title</th>";
			echo "        <th>Authors</th>";
			echo "        <th>Journal</th>";
			echo "        <th>Year</th>";
			echo "    </tr>";
			foreach ($results as $resultMD) {
				echo "    <tr>";
				echo "        <td>".$resultMD['pmid']."</td>";
				echo '    <td><a href="http://www.ncbi.nlm.nih.gov/pubmed/'.$resultMD['pmid'].'" target="_blank">'.$resultMD['title']."</a></td>";
				echo '        <td>'.implode(", ",$resultMD['authors']).'</td>';
				echo '		  <td>'.$resultMD['journalabbrev'].'</td>';
				echo '        <td>'.$resultMD['year'].'</td>';
				echo '    </tr>';
			}
			echo '</table>';
			echo '</div>';
		}
		$cnt++;
		echo '<br> <br>';
	}
	echo "<br>";
	//echo "<h1> count: " .count($mesh_drug_codes).'</h1>';
	if (count($mesh_drug_codes) > 1) {
		// Let's look at the interactions between multiple drugs
		echo "<h3> Adverse event literature featuring all drugs (not necesarily a complementary interaction): ";
		$drugs_total=0;
		$medline_query=$ADR_query.' AND "';
		foreach ($mesh_drug_codes as $mesh_item) {
			if ($drugs_total >0 ){
				echo ", ";
			}
			if ($drugs_total>=1) {
				$medline_query=$medline_query. ' AND "';
			}
			echo "<a href='http://mesh.bio2rdf.org/describe/?url=http%3A%2F%2Fbio2rdf.org%2Fmesh%3A".$mesh_item."'>".$mesh_drug_names[$drugs_total]."</a>";
			$medline_query=$medline_query.$mesh_drug_names[$drugs_total].'"[Mesh]';
			$drugs_total=$drugs_total+1;
		}
		echo "</h3>";
			echo '<div id="medline_results"> ';
			//Lets look for the medline results for each drug
			//$term = $mesh_drug_names[$cnt];
			//$ADR_query='"adverse effects"[Subheading] AND "chemically induced"[Subheading] AND "Chemicals and Drugs Category"[Mesh]';
			//$query_string=urlencode('"Chemicals and Drugs Category/adverse effects"[Mesh] AND "chemically induced"[Subheading] AND "Drug interactions"[Mesh]');
			$query_string=$medline_query;
			//echo '<p>'.$query_string.'</p>';
			$result = file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&retmax=5&term='.urlencode($query_string).'&retmode=xml');
			$xml = simplexml_load_string($result);
			//
			$total_hits=$xml->Count;
			if ($total_hits == 0) {
				echo '<p> No results found for '.$medline_query.'<p>';
			} else {		
				$results = array();
					if (isset($xml->IdList->Id) && !empty($xml->IdList->Id)) {
						$ids = array();
						foreach ($xml->IdList->children() as $id) {
							$ids[] = (string)$id;
						}
						$results = query_pmid(implode(',',$ids), $compact);
					}
				$tr_results=$xml->Count;
				if ($tr_results < 5) {
					$recent=$tr_results;
				} else {
					$recent=5;
				}
				
				echo "<p>Search results for <strong>".urldecode($query_string)."</strong> (".$tr_results." results, showing the most recent ".$recent.")</p>";
				echo '<table border="0" cellspacing="0" cellpadding="0">';
				echo "   <tr>";
				echo "      <th>PMID</th>";
				echo "      <th>Title</th>";
				echo "        <th>Authors</th>";
				echo "        <th>Journal</th>";
				echo "        <th>Year</th>";
				echo "    </tr>";
				foreach ($results as $resultMD) {
					echo "    <tr>";
					echo "        <td>".$resultMD['pmid']."</td>";
					echo '    <td><a href="http://www.ncbi.nlm.nih.gov/pubmed/'.$resultMD['pmid'].'" target="_blank">'.$resultMD['title']."</a></td>";
					echo '        <td>'.implode(", ",$resultMD['authors']).'</td>';
					echo '		  <td>'.$resultMD['journalabbrev'].'</td>';
					echo '        <td>'.$resultMD['year'].'</td>';
					echo '    </tr>';
				}
				echo '</table>';
		}
		echo '</div>';	
	
	} 
	echo "<br>";
	echo "<br>";
	echo "</body>";
	echo "</html>";
	

?>