<?php
	$term = stripslashes(urldecode($_POST['term']));
	$proxy_name = '';
	$proxy_port = '';
	$proxy_username = '';
	$proxy_password = '';
	$curl_site_url = '';
    //$query_string=urlencode('"Chemicals and Drugs Category/adverse effects"[Mesh] AND "chemically induced"[Subheading] AND "Drug interactions"[Mesh]');
	$query_string=$term;
$result = file_get_contents('http://eutils.ncbi.nlm.nih.gov/entrez/eutils/esearch.fcgi?db=pubmed&term='.$query_string.'&retmode=xml');
$xml = simplexml_load_string($result);
//echo $xml->Count; // = 5986

		//$this=$xml;
		//print_r($xml);
		//$this->count = (int)$xml->Count;
		
		// esearch returns a list of IDs so we have to concatenate the list and do an efetch
		$results = array();
		if (isset($xml->IdList->Id) && !empty($xml->IdList->Id)) {
			$ids = array();
			foreach ($xml->IdList->children() as $id) {
				$ids[] = (string)$id;
			}
			$results = query_pmid(implode(',',$ids), $compact);
		}
		//print_r($results);

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
?>

<!-- OUTPUT STUFF -->

<?php if (!empty($results)): ?>
	<p>Search results for <strong><?php echo urldecode($query_string); ?></strong> (<?php echo $xml->Count; ?> results, showing the top 20)</p>
	<table border="0" cellspacing="0" cellpadding="0">
		<tr>
			<th>PMID</th>
			<th>Title</th>
			<th>Authors</th>
			<th>Journal</th>
			<th>Year</th>
		</tr>
		<?php foreach ($results as $result): ?>
		<tr>
			<td><?php echo $result['pmid']; ?></td>
			<td><a href="http://www.ncbi.nlm.nih.gov/pubmed/<?php echo $result['pmid']; ?>" target="_blank"><?php echo $result['title']; ?></a></td>
			<td><?php echo implode(", ",$result['authors']); ?></td>
			<td><?php echo $result['journalabbrev']; ?></td>
			<td><?php echo $result['year']; ?></td>
		</tr>
		<?php endforeach; ?>
	</table>
<?php endif; ?>