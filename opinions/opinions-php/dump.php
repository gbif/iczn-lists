<?php

// Opinions

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/lib.php');

require_once(dirname(__FILE__) . '/clean.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	
$sql = 'SELECT * FROM ' . $config['db_table']; // . ' LIMIT 5';

$meta = array
(
	"id" => "http://purl.org/dc/terms/identifier",
	"creator" => "http://purl.org/dc/terms/creator",
	"title" => "http://eol.org/schema/reference/primaryTitle",
	"journal" => "http://purl.org/dc/terms/title",
	"issn" => "http://purl.org/ontology/bibo/issn",
	"oclc" => "http://purl.org/ontology/bibo/oclcnum",
	"volume" => "http://purl.org/ontology/bibo/volume",
	"issue" => "http://purl.org/ontology/bibo/issue",
	"spage" => "http://purl.org/ontology/bibo/pageStart",
	"epage" => "http://purl.org/ontology/bibo/pageEnd",
	
	"biostor" => "http://purl.org/ontology/bibo/uri",
	"bhl" => "http://purl.org/ontology/bibo/uri"
);

$dwca = array();
$ris = '';

$result = $db->Execute($sql);
if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
while (!$result->EOF) 
{
	$record = new stdclass;
	
	$record->id = $result->fields['Number'];
	$record->type = $result->fields['Type'];

	$record->creator = 'International Commission on Zoological Nomenclature';
	$record->title = clean_text($result->fields['Title']);		
	$record->journal = $result->fields['Journal'];
	
	$record->issn = $result->fields['ISSN'];
	$record->oclc = $result->fields['OCLC'];

	
	$record->volume = $result->fields['Volume'];
	$record->issue = $result->fields['Issue'];
	$record->spage = $result->fields['Spage'];
	$record->epage = $result->fields['Epage'];	
	$record->year = $result->fields['Year'];

	$record->biostor = $result->fields['Biostor'];
	if ($record->biostor != '')
	{
		$record->biostor = 'http://biostor.org/reference/' . $record->biostor;
	}
	
	$record->bhl = $result->fields['BHL'];
	if ($record->bhl != '')
	{
		$record->bhl = 'http://biodiversitylibrary.org/page/' . $record->bhl;
	}
	
	
	
	
	
	//print_r($record);
	
	// RIS
	
	//$ris = '';
	$ris .= "TY  - JOUR\n";
	$ris .= "AU  - " . $record->creator . "\n";
	$ris .= "TI  - " . $record->title . "\n";
	
	$ris .= "JO  - " . $record->journal . "\n";
	if ($record->issn != "")
	{
		$ris .= "SN  - " . $record->issn . "\n";
	}
	$ris .= "VL  - " . $record->volume . "\n";
	$ris .= "IS  - " . $record->issue . "\n";
	$ris .= "SP  - " . $record->spage . "\n";
	$ris .= "EP  - " . $record->epage . "\n";
	$ris .= "Y1  - " . $record->year . "\n";

	if ($record->biostor != "")
	{
		$ris .= "UR  - " . $record->biostor;
	}
	if ($record->bhl != "")
	{
		$ris .= ";" . $record->bhl;
	}
	$ris .= "\n";
	
	if ($record->type == "Direction")
	{
		$ris .= "KW  - Direction\n";
	}
	else
	{
		$ris .= "KW  - Opinion\n";
	}	
	
	$ris .= "ER  -\n\n";
	
	//echo $ris;
	
			
	if (count($dwca) == 0)
	{
		$kv = array();
		foreach ($record as $k => $v)
		{
			switch ($k)
			{
		
				case 'id':
					$kv[] = $k;
					break;
				case 'type':
					break;
			
				default:
					$kv[] = $k;
					break;
			}	
		}
		$dwca[] = $kv;
	}
	
	
	// DwC
	$kv = array();
	foreach ($record as $k => $v)
	{
		switch ($k)
		{
			case 'id':
				if ($record->type == 'Opinion')
				{
					$kv[] = 'o' . $record->id;
				}
				else
				{
					$kv[] = 'd' . $record->id;				
				}
				break;
			case 'type':
				break;
			
			default:
				$kv[] = $v;
				break;
		}
	}
	$dwca[] = $kv;
	//echo join("|", $kv) . "\n";
	
	
	
	
	$result->MoveNext();	

}

// RIS
file_put_contents('opinions.ris', $ris);

// DwC meta
$key = '';
foreach ($meta as $k => $v)
{
	$key .= $k . '=' . $v . "\n";
}
file_put_contents('headings.txt', $key);


// DwC 
$d = '';
foreach ($dwca as $row)
{
	$d .= join("\t", $row);
	$d .= "\n";
}
file_put_contents('opinions.txt', $d);




		
?>