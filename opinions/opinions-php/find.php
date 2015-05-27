<?php

// Opinions

require_once(dirname(__FILE__) . '/config.inc.php');
require_once(dirname(__FILE__) . '/adodb5/adodb.inc.php');
require_once(dirname(__FILE__) . '/lib.php');

//--------------------------------------------------------------------------------------------------
function get_hit($obj)
{
	$PageID = 0;
	
	$h = -1;
	$threshold = 0.9;
	$n = count($obj);
	for($k=0;$k<$n;$k++)
	{
		if ($obj[$k]->score > $threshold)
		{
			$h = $k;
		}
	}

	if (($h != -1) )
	{		
		$PageID = $obj[$h]->PageID;
	}
	
	return $PageID;
}


//--------------------------------------------------------------------------------------------------
function get_title($PageID, &$record)
{

	$url = 'http://biostor.org/bhlapi_page_text.php?PageID=' . $PageID;

	$text = get($url);
	
	//echo $text;

	$lines = explode("\n", $text);

	$state = 0;

	$title = '';
	$issue = '';
	$date = '';


	foreach ($lines as $line)
	{
		switch ($state)
		{
			case 0:
				if (preg_match('/^OPINION\s+(?<opinion>\d+)/', $line, $m))
				{
					$title = 'Opinion ' .  $m['opinion'] . ' ';
					$state = 1;
				}
				if (preg_match('/^DIRECTION\s+(?<opinion>\d+)/', $line, $m))
				{
					$title = 'Direction ' .  $m['opinion'] . ' ';
					$state = 1;
				}
				
				// [10] =>  VOLUME 11. Part 23. Pp. 359—368 
				if (preg_match('/VOLUME\s+\d+\.\s+Part\s+(?<issue>\d+)\./', $line, $m))
				{
					$issue = $m['issue'];
				}
				break;
		
			case 1:
				if (preg_match('/^LONDON/', $line, $m))
				{
					$state = 2;
				}
				else
				{
					//echo "|$line|\n";
					$line = preg_replace('/\-\s+$/', '', $line);
					if ($line != '')
					{
						$title .= $line;
					}
				}
				break;
		
			case 2:
				// Issued 2nd December, 1955 
				if (preg_match('/Issued\s+(?<date>.*)/', $line, $m))
				{
					$str = $m['date'] ;
					$str = str_replace(',', '', $str);
					echo $m['date'] . "\n";
					if (($timestamp = strtotime($str)) === false) 
					{
						echo 'Failed to parse "' . $str . '"' . "\n";
					}
					else
					{
						$date = date('Y-m-d', $timestamp);
					}
				}
	
	
				break;
		
			default:
				break;
		}
		


	}


	// process

	$title = preg_replace('/\s\s+/', ' ', $title);
	$record->atitle = $title;
	$record->issue =  $issue;
	$record->date = $date;
	
	//echo $date . "\n";
	//exit();

}

//--------------------------------------------------------------------------------------------------
function build_openurl(&$record)
{
	$parts = array();
	$parts['genre'] = 'article';
	$parts['title'] = $record->journal;
	$parts['volume'] = $record->volume ;
	$parts['issue'] = $record->issue ;
	
	$parts['spage'] = $record->spage ;
	$parts['epage'] = $record->epage ;
	$parts['year'] = $record->year ;
	
	$parts['date'] = $record->date ;
	
	$parts['atitle'] = $record->atitle;
	
	$record->openurl = 'http://biostor.org/openurl?' . http_build_query($parts);
	
	$record->openurl = str_replace('+', '%20', $record->openurl);
}

//--------------------------------------------------------------------------------------------------
$db = NewADOConnection('mysql');
$db->Connect("localhost", 
	$config['db_user'] , $config['db_passwd'] , $config['db_name']);

// Ensure fields are (only) indexed by column name
$ADODB_FETCH_MODE = ADODB_FETCH_ASSOC;

	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Opinions and declarations rendered by the International Commission on Zoological Nomenclature"';
	
	// offset, per page
	$sql .= ' LIMIT 420,100';

	$opinions = array();

	$result = $db->Execute($sql);
	if ($result == false) die("failed [" . __LINE__ . "]: " . $sql);
	while (!$result->EOF) 
	{
		$record = new stdclass;
		$record->new_title = false;
		$record->new_openurl = false;
		$record->new_cleaned = false;
		
		$record->id = $result->fields['Number'];
		$record->type = $result->fields['Type'];
		$record->journal = $result->fields['Journal'];
		$record->volume = $result->fields['Volume'];
		
		$record->issue = $result->fields['Issue'];
		$record->spage = $result->fields['Spage'];
		$record->epage = $result->fields['Epage'];	
		$record->year = $result->fields['Year'];
		
		if ($result->fields['OpenURL'])
		{
			$record->openurl = $result->fields['OpenURL'];
		}
		else
		{
			$record->atitle = $record->type . ' ' .  $record->id;
			
			build_openurl($record);
			/*
			$parts = array();
			$parts['genre'] = 'article';
			$parts['title'] = str_replace(' ', '%20', $record->journal);
			$parts['volume'] = $record->volume ;
			
			
			
			$parts['spage'] = $record->spage ;
			$parts['epage'] = $record->epage ;
			$parts['year'] = $record->year ;
			
			$parts['atitle'] = $record->type . '%20' .  $record->id;
			
			$record->new_openurl = true;
			
			$record->openurl = 'http://biostor.org/openurl?' . http_build_query($parts);
			
			$record->openurl = str_replace('%2520', '%20', $record->openurl);
			*/
		}
		
		// 1. Look up in BioStor

		$url = $record->openurl . '&format=json';
		
		//echo $url . "\n";
		
		$url = str_replace(' ', '%20', $url);
		$json = get($url);
			
		if ($json != '')
		{
			$obj = json_decode($json);
			
			//print_r($obj);
			
			if (isset($obj->reference_id))
			{
				// found
				echo "Found\n";
			}
			else
			{
			    // try and fetch
			    
			    $PageID = get_hit($obj);
			    
			    if ($PageID != 0)
			    {
			    	// yes
					echo "Hit $PageID\n";
										
					get_title($PageID, $record);
					
					//print_r($record);					

					build_openurl($record);
					$url = $record->openurl . '&format=json';
					
					echo $url . "\n";
					
					$json = get($url);
					
					echo $json;
			
					if ($json != '')
					{
						$obj = json_decode($json);
			
						 $PageID = get_hit($obj);
						 if ($PageID != 0)
						 {
						 	echo "Located\n";
						 	
						 	$url = $record->openurl . '&format=json' . '&id=http://biodiversitylibrary.org/page/' . $PageID;
						 	//echo $url . "\n";
						 	$json = get($url);
						 	echo $json;
						 	
						 }
						
					}
										
					
					
					/*
					// 6. We have a hit, construct OpenURL that forces BioStor to save
					$openurl .= '&id=http://www.biodiversitylibrary.org/page/' . $x[$h]->PageID;
					$url = 'http://biostor.org/openurl.php?' . $openurl . '&format=json';

					$json = get($url);
					$j = json_decode($json);
					$found = $j->reference_id;
					*/
				}
			}			    
		}
		$result->MoveNext();	
	
	}

		
?>