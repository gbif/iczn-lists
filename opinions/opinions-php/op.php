<?php

require_once('lib.php');

$PageID = 34650172;

$url = 'http://biostor.org/bhlapi_page_text.php?PageID=' . $PageID;

$text = get($url);

echo $text;

$lines = explode("\n", $text);
print_r($lines);

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
				if (($timestamp = strtotime($m['date'])) === false) 
				{
    				echo 'Failed to parse "' . $m['date'] . '"' . "\n";
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
echo $title . "\n";
echo $issue . "\n"; 
echo $date . "\n"; 
?>
