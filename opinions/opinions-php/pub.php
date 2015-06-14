<?php

require_once(dirname(__FILE__) . '/lib.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');

require_once(dirname(__FILE__) . '/CiteProc.php');



//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;



//--------------------------------------------------------------------------------------------------
// Use content negotian and citeproc, may fail with some DOIs
// e.g. [14/12/2012 12:52] curl --proxy wwwcache.gla.ac.uk:8080 -D - -L -H   "Accept: text/x-bibliography; style=apa" "http://dx.doi.org/10.1080/03946975.2000.10531130" 

function get_formatted_citation_from_doi($doi)
{
	
//	$url = 'http://data.crossref.org/' . $doi;
	$url = 'http://dx.doi.org/' . $doi;
	$text = get($url, '', "text/x-bibliography; style=apa");
	
	return $text;
}

//--------------------------------------------------------------------------------------------------
function get_formatted_citation_from_cinii($cinii)
{
	global $db;
	
	$html = '';
	
	$sql = 'SELECT * FROM cinii WHERE naid = ' . $cinii . ' LIMIT 1';

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$html .= $result->fields['title'];
		$html .= '<img src="' . $result->fields['thumbnail'] . '" />';
	}
	
	return $html;
}


//--------------------------------------------------------------------------------------------------
function get_formatted_citation_from_biostor($biostor)
{
	$url = 'http://biostor.org/reference/' . $biostor . '.citeproc';

	$json = get($url);
		
	$citeproc_obj = json_decode($json);
	
	if (count($citeproc_obj->author) == 0)
	{
		$a = new stdclass;
		//$a->given = '';
		$a->literal = 'Anomymous';
		$citeproc_obj->author[] = $a;
		
	}
	
	
	$csl = file_get_contents(dirname(__FILE__) . '/style/apa.csl');

	$citeproc = new citeproc($csl);
	$html = $citeproc->render($citeproc_obj, 'bibliography');
	
	return $html;
}

$doi == '';
$cinii == '';

if (isset($_GET['doi']))
{
	$doi = $_GET['doi'];
	
	$data = new stdclass;
	$data->html = get_formatted_citation_from_doi($doi);
	
	echo json_encode($data);
	exit();
		
}

if (isset($_GET['cinii']))
{
	$cinii = $_GET['cinii'];
	
	$data = new stdclass;
	$data->html = get_formatted_citation_from_cinii($cinii);
	
	echo json_encode($data);
	exit();
		
}

if (isset($_GET['biostor']))
{
	$biostor = $_GET['biostor'];
	
	$data = new stdclass;
	$data->html = get_formatted_citation_from_biostor($biostor);
	
	echo json_encode($data);
	exit();
		
}

?>