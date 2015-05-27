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


//--------------------------------------------------------------------------------------------------
function default_display()
{
	global $config;
	
	echo '<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
    <style type="text/css">
      body { margin: 20px; font-family:sans-serif;}
      input[type="text"] {
    		font-size:14px;
	  }
	  button {font-size:14px;}
    </style>


		<title>' . $config['site_name'] . '</title>
	</head>
	<body>
		<h1>Opinions</h1>
	</body>
</html>';
}


//--------------------------------------------------------------------------------------------------
function display_search($query = 1900)
{
	global $config;
	global $db;
	
	
	$query = trim(mysql_escape_string($query));
	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Number >= ' . $query . ' LIMIT 100';
	
	// Smithsonian Miscellaneous Collections
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Smithsonian Miscellaneous Collections"';
	
	// BZN directions 
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Bulletin of Zoological Nomenclature" AND Type="Direction"';
	
	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Opinions and declarations rendered by the International Commission on Zoological Nomenclature"';
//	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Number=6';
	
	// BZN opinions
	//$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Bulletin of Zoological Nomenclature" AND Type="Opinion" AND Number <>608';
	//$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Bulletin of Zoological Nomenclature" AND BHL IS NULL';

	//$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Journal="Bulletin of Zoological Nomenclature" AND Type="Opinion" AND Number IN (580,581,582)';
	
	$sql = 'SELECT * FROM ' . $config['db_table'] . ' WHERE Number=67';
	
	
	// offset, per page
	//$sql .= ' LIMIT 450,50';
	
	$update_sql = '';
	$biostor_sql = '';
	
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
		
		/*
		if (preg_match('/(?<volume>\d+)\s+\((?<issue>\d+)\)/', $record->volume, $m))
		{
			$record->volume = $m['volume'];
			$record->issue = $m['issue'];
			
			$update_sql .= 'UPDATE opinions SET Volume=' . $record->volume . ' WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
			$update_sql .= 'UPDATE opinions SET Issue="' . $record->issue . '" WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
			
			
			
		}
		*/
		
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
		}
		
		if ($result->fields['Title'])
		{
			$record->title = utf8_encode($result->fields['Title']);
		}		
		if ($result->fields['CleanedTitle'])
		{
			$record->cleanedTitle = utf8_encode($result->fields['CleanedTitle']);
		}		

		if ($result->fields['BHL'] != '')
		{
			$record->bhl = $result->fields['BHL'];
		}		
		
				
		if ($result->fields['Biostor'] != '')
		{
			$record->biostor = $result->fields['Biostor'];
			
			if (!isset($record->title) || !isset($record->bhl))
			{
				$url = 'http://biostor.org/reference/' . $record->biostor . '.json';
				
				$json = get($url);
			
				if ($json != '')
				{
					$obj = json_decode($json);
					/*
					echo '<pre>';
					echo $record->biostor . "\n";
					echo $record->bhl . "\n";
					print_r($obj->bhl_pages);
					echo '</pre>';
					*/
					
					$record->title = $obj->title;
			
					$record->title = preg_replace('/\n/u', ' ', $record->title);
					
					if (isset($obj->issue))
					{
						$record->issue = $obj->issue;
						if ($record->issue != '')
						{
							$update_sql .= 'UPDATE opinions SET issue="' . $record->issue . '" WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";						
						}
					}
			
					$record->new_title = true;
					$record->new_cleaned = true;
					
					if (!isset($record->bhl))
					{	
						$record->bhl = $obj->bhl_pages[0];
						$update_sql .= 'UPDATE opinions SET BHL=' . $record->bhl . ' WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
					}
			
			
					$update_sql .= 'UPDATE opinions SET Biostor=' . $record->biostor . ' WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
					$update_sql .= 'UPDATE opinions SET Title="' . addcslashes($record->title, '"') . '" WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
					
				}
			}
			
		}
		else
		{
			$url = $record->openurl . '&format=json';
			//$url = str_replace('&', '&amp;', $url);
			$url = str_replace(' ', '%20', $url);
			//$url = str_replace('%2520', '%20', $url);
			//echo $url . '<br />';
			$json = get($url);
			//echo htmlentities($json);
			
			if ($json != '')
			{
				$obj = json_decode($json);
				
				if (isset($obj->reference_id))
				{
					$record->biostor = $obj->reference_id;
					$record->title = $obj->title;
				
					$record->title = preg_replace('/\n/u', ' ', $record->title);
				
					$record->new_title = true;
					$record->new_cleaned = true;
					
					$record->bhl = $obj->PageID;
					
					$record->issue = $obj->issue;
					if ($record->issue != '')
					{
						$update_sql .= 'UPDATE opinions SET issue="' . $record->issue . '" WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";						
					}
					
					$update_sql .= 'UPDATE opinions SET BHL=' . $record->bhl . ' WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
					
				
					$update_sql .= 'UPDATE opinions SET Biostor=' . $record->biostor . ' WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
					$update_sql .= 'UPDATE opinions SET Title="' . addcslashes($record->title, '"') . '" WHERE Number=' . $record->id . ' AND Journal="' . $record->journal . '";' . "\n";
				}				
			}
			
		}
		
		if ($result->fields['CleanedTitle'])
		{
			$record->cleanedTitle = utf8_encode($result->fields['CleanedTitle']);
		}
		else
		{
			$record->new_cleaned = true;
			$record->cleanedTitle = $record->title;
			
			$record->cleanedTitle = clean_text($record->cleanedTitle);
		
			//$update_sql .= 'UPDATE opinions SET CleanedTitle="' . addcslashes($record->cleanedTitle, '"') . '" WHERE Number=' . $record->id . ';' . "\n";
			
		
		}
		
		// print_r($record);
		
		if (isset($record->biostor))
		{
			// Title
			if (isset($record->cleanedTitle) && ($record->cleanedTitle != ''))
			{
				$biostor_sql .= 'UPDATE rdmp_reference SET title="' . addcslashes($record->cleanedTitle, '"') . '" WHERE reference_id=' . $record->biostor . ';' . "\n";
			}
			// Issue
			if (isset($record->issue))
			{
				$biostor_sql .= 'UPDATE rdmp_reference SET issue="' . addcslashes($record->issue, '"') . '" WHERE reference_id=' . $record->biostor . ';' . "\n";
			}
			// Journal
			$biostor_sql .= 'UPDATE rdmp_reference SET secondary_title="' . addcslashes($record->journal, '"') . '" WHERE reference_id=' . $record->biostor . ';' . "\n";
			
			
			// Author
			$biostor_sql .= 'DELETE FROM rdmp_author_reference_joiner WHERE reference_id=' . $record->biostor . ';' . "\n";
			$biostor_sql .= 'INSERT INTO rdmp_author_reference_joiner(author_id, reference_id, author_order) VALUES(1893,' . $record->biostor . ', 1);' . "\n";
			
		}
		
				

		
		
		$opinions[] = $record;
		$result->MoveNext();	
	
	}
	
	// Display...
	echo 
'<html>
	<head>
		<meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
		<base href="' . $config['web_root'] . '" />
    <style type="text/css">
      body { margin: 20px; font-family:sans-serif;}
      input[type="text"] {
    		font-size:14px;
	  }
	  button {font-size:14px;}
    </style>

		<script type="text/javascript" src="' . $config['web_root'] . 'js/jquery-1.4.4.min.js"></script>
		<title>' .  $config['site_name'] . '</title>
		
		<script>
		
			function show_doi(doi)
			{
				$("#details").html("");
				$.getJSON("pub.php?doi=" + encodeURIComponent(doi),
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_cinii(cinii)
			{
				$("#details").html("");
				$.getJSON("pub.php?cinii=" + cinii,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_biostor(biostor)
			{
				$("#details").html("");
				$.getJSON("pub.php?biostor=" + biostor,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
				//$("#details").html("xxx");
			}
			
			function show_bhl(PageID, term)
			{
				$("#details").html("");
				$.getJSON("bhl.php?PageID=" + PageID + "&term=" + term,
					function(data){
						var html = data.html;
						$("#details").html(html);
					}
					
				);	
			}
			
			
			
		</script>
	</head>
	<body>';
	
	
	echo '<table cellspacing="0">';
	echo '<tbody style="font-size:12px;">';
	
	echo '<tr>';
	echo '<th>Number</th>';
	echo '<th>Journal</th>';
	echo '<th>Volume</th>';
	echo '<th>Issue</th>';
	echo '<th>Spage</th>';
	echo '<th>EPage</th>';
	echo '<th>Year</th>';
	echo '<th>OpenURL</th>';
	echo '<th>Biostor</th>';
	echo '<th>BHL</th>';
	echo '<th>Title</th>';
	echo '<th>Cleaned title</th>';
	echo '<th>Citation</th>';
	echo '</tr>';
	
	
	$odd = true;
	
	foreach ($opinions as $sp)
	{
		echo '<tr';
		
		
		if ($odd)
		{
			echo ' style="background-color:#eef;"';
			$odd = false;
		}
		else
		{
			echo ' style="background-color:#fff;"';
			$odd = true;
		}
		
		
		echo '>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->id . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->journal . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->volume . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->issue . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->spage . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->epage . '</span>' . '</td>';
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->year . '</span>' . '</td>';
		
		echo '<td>';
		if ($record->new_openurl)
		{
			echo '<span style="color:red;">';
		}
		else
		{
			echo '<span style="color:rgb(128,128,128);">';
		}
		echo  '<a href="' . $sp->openurl . '" target=_new">' . 'OpenURL' . '</a></span>' . '</td>';
		
		
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . '<a href="http://biostor.org/reference/' . $sp->biostor . '" target="_new">' . $sp->biostor . '</a>' . '</span>' . '</td>';
		
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . '<a href="http://biodiversitylibrary.org/page/' . $sp->bhl . '" target="_new">' . $sp->bhl . '</a>' . '</span>' . '</td>';
		
		if ($record->new_title)
		{
			echo '<td>' . '<span style="color:red;">' . $sp->title . '</span>' . '</td>';		
		}
		else
		{		
			echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->title . '</span>' . '</td>';
		}
		if ($record->new_cleaned)
		{
			echo '<td>' . '<span style="color:green;">' . $sp->cleanedTitle . '</span>' . '</td>';
		
		}
		else
		{
			echo '<td>' . '<span style="color:rgb(128,128,128);">' . $sp->cleanedTitle . '</span>' . '</td>';
		}
		
		$citation = '';
		
		if (0)
		{
			if (isset($sp->biostor))
			{
				$url = $config['web_server'] . $config['web_root'] . 'pub.php?biostor=' .  $sp->biostor;
				//echo $url;
				$json = get($url);
				if ($json != '')
				{
					$obj = json_decode($json);
					$citation = $obj->html;
				}
			}
		}		
		
		echo '<td>' . '<span style="color:rgb(128,128,128);">' . $citation . '</span>' . '</td>';
		

		
		
		echo '</tr>';
	}
	echo '</tbody>';
	echo '</table>';
	echo '</div>';
	
	
	//echo '<div style="clear:both"></div>';
	echo '<div style="font-size:10px;width:800px;">' . $config['credits'] . '</div>';
	
	
	echo '</div>';
	
	echo '<pre style="font-size:10px;">';
	echo $update_sql;
	echo '</pre>';

	echo '<pre style="font-size:10px;background-color:orange;">';
	echo $biostor_sql;
	echo '</pre>';
	
	echo
'	</body>
</html>';

}




//--------------------------------------------------------------------------------------------------
function main()
{
	global $config;
	global $debug;
	
	$query = '';
		
	// If no query parameters 
	if (count($_GET) == 0)
	{
		//default_display();
		display_search(1900);
		exit(0);
	}
	
	if (isset($_GET['q']))
	{
		$query = $_GET['q'];
		display_search($query);
	}

}


main();
		
?>