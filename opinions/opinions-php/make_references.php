<?php

// read names, locate opinion, export for DwCA

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/lib.php');

require_once(dirname(__FILE__) . '/clean.php');
require_once(dirname(__FILE__) . '/pub.php');

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

function find($id, $type='Opinion', $isAddendum="FALSE")
{
	global $config;
	global $db;
	
	$record = null;
	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Number="' . $id . '" AND Type="' . $type . '" AND IsAddendum="' . $isAddendum . '"  LIMIT 1'; 
	

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	
	if ($result->NumRows() == 1)
	{
		$record = new stdclass;
		
		if (0)
		{
			$record->biostor = $result->fields['Biostor'];
			if ($record->biostor != '')
			{
				$record->citation = get_formatted_citation_from_biostor($record->biostor);
			}			
		}
		else
		{
			$record->author = 'International Commission on Zoological Nomenclature';
			$record->title = clean_text($result->fields['Title']);
		
			$record->journal = $result->fields['Journal'];
	
			$record->volume = $result->fields['Volume'];
			$record->issue = $result->fields['Issue'];
			$record->spage = $result->fields['Spage'];
			$record->epage = $result->fields['Epage'];	
			$record->year = $result->fields['Year'];
		
			$record->source = $record->journal;
			if ($record->volume != '')
			{
				$record->source .= ' ' . $record->volume;
			}
			if ($record->issue != '')
			{
				$record->source .= '(' . $record->issue . ')';
			}
			if ($record->spage != '')
			{
				$record->source .= ', ' . $record->spage;
				if ($record->epage != '')
				{
					$record->source .= '-' . $record->epage ;
				}
			}
			if ($record->year != '')
			{
				$record->source .= ' (' . $record->year . ')';
			}
		
			$record->citation = $record->author . '. ' . $record->title . '.' . $record->source;

			$record->biostor = $result->fields['Biostor'];
			if ($record->biostor != '')
			{
				$record->biostor = 'http://biostor.org/reference/' . $record->biostor;
			}	
		}
	}
	
	return $record;
}

$not_found = array();

$filename = '../../names.txt';

$file_handle = fopen($filename, "r");
while (!feof($file_handle)) 
{
	$row = fgets($file_handle);
	$parts = explode("\t",$row);
	
	//print_r($parts);
	
	$obj = new stdclass;
	$obj->id = $parts[0];
	$obj->name = $parts[1];
	
	$obj->text = $parts[20];
	if ($obj->text == '\N')
	{
		$obj->text = $parts[19];
	}
	$obj->text = preg_replace('/\s+$/u', '', $obj->text);
	
	$obj->opinion = array();
	$obj->direction = array();
	$obj->addendum = array();
	
	$matched = false;
	if (!$matched)
	{
		if (preg_match('/Op\.\s*(?<number>\d+)[,]?$/', $obj->text, $m))
		{
			$obj->opinion[] = $m['number'];
			$matched = true;			
		}
	}
	if (!$matched)
	{		
		// Op. 77, Op.1427
		// Op. 66, Op. 201, 	
		if (preg_match('/Op\.\s*(?<number1>\d+),\s+Op\.\s*(?<number2>\d+)$/', $obj->text, $m))
		{
			$obj->opinion[] = $m['number1'];
			$obj->opinion[] = $m['number2'];
			$matched = true;			
		}
	}
		
	if (!$matched)
	{		
		// Op. 643, Addendum to   Op. 643 (Bulletin of Zoological Nomenclature, 21: 92)
		if (preg_match('/Op\.\s*(?<number1>\d+),\s+(Addendum|Appendix)\s+to\s+Op\.\s*(?<number2>\d+)\s+/', $obj->text, $m))
		{
			$obj->opinion[] = $m['number1'];
			$obj->addendum[] = $m['number2'];
			$matched = true;
		}
	}
	
	// Op. 2000; original entry amended as per BZN 67: 118
	if (!$matched)
	{		
		// Op. 643, Addendum to   Op. 643 (Bulletin of Zoological Nomenclature, 21: 92)
		if (preg_match('/Op\.\s*(?<number>\d+);/', $obj->text, $m))
		{
			$obj->opinion[] = $m['number'];
			$matched = true;
		}
	}
	
	// Op. 2222.
	if (!$matched)
	{		
		if (preg_match('/Op\.\s*(?<number>\d+)\./', $obj->text, $m))
		{
			$obj->opinion[] = $m['number'];
			$matched = true;
		}
	}
	
	// Op. 1368, Corrigenda in Bulletin of Zoological Nomenclature, 45: 304
	if (!$matched)
	{		
		if (preg_match('/Op\.\s*(?<number>\d+), Corrigenda/', $obj->text, $m))
		{
			$obj->opinion[] = $m['number'];
			$matched = true;
		}
	}
	
		
	if (!$matched)
	{		
		if (preg_match('/Direction\s+(?<number>\d+)$/', $obj->text, $m))
		{
			$obj->direction[] = $m['number'];
			$matched = true;			
		}

	}
	
	//print_r($obj);
	//echo "|" . $obj->text . "|\n";
	if (!$matched) 
	{
		echo "Not parsed\n";
		exit();
	}
	
	$found = false;
	
	foreach ($obj->opinion as $id)
	{
		$record = find($id, 'Opinion');
		
		if ($record)
		{
			$obj->record[] = $record;
			$found = true;
		}
		else
		{
			$not_found[] = $obj;
		}
	}
	
	foreach ($obj->direction as $id)
	{
		$record = find($id, 'Direction');
		
		if ($record)
		{
			$obj->record[] = $record;
			$found = true;
		}
		else
		{
			$not_found[] = $obj;
		}
	}
	
	
	foreach ($obj->addendum as $id)
	{
		$record = find($id, 'Opinion', 'True');
		
		if ($record)
		{
			$obj->record[] = $record;
			$found = true;
		}
		else
		{
			$not_found[] = $obj;
		}
	}
	
	if ($found)
	{
		foreach ($obj->record as $record)
		{
			echo $obj->id;
			echo "\t" . $record->biostor;
			echo "\t" . $record->citation;
			echo "\t" . $record->title;
			echo "\t" . $record->author;
			echo "\t" . $record->year;
			echo "\t" . $record->source;
			echo "\t" . "publication";
			echo "\n";
		}
	}
		
	
	
	//print_r($obj);
	
	// dump
	
	
	//if ($obj->id == 4233) exit();



}

print_r($not_found);

?>