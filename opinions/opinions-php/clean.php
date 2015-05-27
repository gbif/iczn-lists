<?php

// clean text

function clean_text($str)
{
	$terms=array(
	 		'A', 'Accustomed', 'And', 'As', 'Assigned','Aside','Attributed','An','Already','Approval','Adopted',
	 		'Anonymously', 'Are','Amplification',
  		'By','Be','Based','Books','Been','Basis','Authorship','Both','Believed','Belonging','Benefit','Bionomial',
        'Certain','Common','Consequential','Collection','Catalogue','Citation','Cancellation','Close',
		'Date', 'Des', 'Der','Despite','De','Divisions','Duly',
		'Emended', 'Et','Editions','Ex','Einer','End','Exclusive','Emend','Emend.','Error','Entitled','Either',
		'For', 'From','Favour','Following','First','Further','Firm','Foregoing','Formed','Formerly','Fossil',
        'Given','Grant','Gender','Genera',
        'His','Have',
		'In', 'Is','Interpreting','Incidental','Including','Invalid',
		'Long-neglected',
        'Known',
        'Made','Meaning','May','Man','Method',
        'Not','Necessity','Non-Marine','Nomenclatorially',
		'Of', 'Or','Origin','Observed','Others',
        'Portions','Proved','Period','Previously','Principles','Providing','Prepared','Parts',
        'Removal','Refusal','Rejection','Rediscovery','Request', 'Rulings','Respectively','Respective', 'Referring',
        'Relating','Reported',
        'Sense','Set','So','Surname','Same','Substitutes','Scientific','Sale','Severally',
		'The', 'To', 'Together','Those','Their','Them','Titles','Than', 'Thereon','That', 'Taxa','This','These','Thereto','They',
		'Stabilisation','Stem','Supplementary','Secure','Simultaneously',
		'Under','Use','Uni','Up',
        'Vs.', 'Var.','Volume',
        'Written','Were',
        'Y','Year',
        
        'Orders',
        
        'Calculated', 'Give', 'Offence', 'Religious','Therewith','Foregoing','Connected','Twenty-three','Assured','Dated',
 
		'Added', 'Addition','Associated','Accordance','Accepted','Availability','Author','Authorised','Authorised', 'Available','Action','Apply','Above',
		'Ammonites','Applications','Amended','Application','Amendment','Applicable',
        'Binomen','Birds','Being',
        'Conserved','Conservation','Currently','Confirmed','Completed','Correct','Correction','Calami','Correcting','Combination','Current','Concerned','Conserving','Classes','Commonly',
        'Continued',
		'Designation','Designations','Designated','Defined','Designate', 'Declined',	'Dealt','Determined','Determination','Description','Designating',
		'Desuetude','Distributed',
        'Emendations', 'Emendation','Existing','Entry','Entries','Erroneous','Each','Eliminate','El','Employment',
        'Family','Fishes','Fixed','Fish','Fixation','Foregoing','Figure','Fallen','Foundation',
        'Class','Cited','Confirmation','Connection',
        'Gen','Group','Generic','Granted',
		'Harmony', 'Holotype', 'Homonym','Homonymy','Homonymys','Having','Held',
        'Interpretation','Its','Included','Intended',
        'Junior','Justified', 
		'Lectotype','Lectotypes','Lapsus', 'Leaflet','Locality',
        'Matters','Misidentified', 'Material', 'Masculine','Microfilm',
		'Name', 'Names', 'Neotype','Nominal', 'Nomenclatural','Nomenclaturally', 'Name-Bearing','Nomenclatorial','Nomenclature','Nibe','Now','Named','Nominate',
		'On','Order', 'Over','Original','Oldest','Older','Other','Originally','Over',
        'Placed','Published','Publication','Pamphlet','Precedence','Priority','Primary','Protected','Place','Purpose','Preserved','Proposed','Preservation','Practice','Phylum','Purposes','Proposal','Provide',
        'Ruling','Ruled','Rules','Recognition','Removing','Replaced','Rejected','Rendered', 'Rediscovered','Restriction','Restricted',
        'Reinstated','Removed','Revision',
        'Read','Replacement','Regarded','Rendering','Reviser','Resolution','Remove','Represent',
		'Senior', 'Species','Suppressed', 'Suppression','Specific','Spelling','Specimen','Status','Suspension','Setting','Spellings','Synonym','Secondary','Separate','Subspecies',
		'Subgeneric','Shall','Similarly','Selections','Selection','Suppress','Stabilize','Superfamily','Similarity','Synonyms','System',
		'Such', 'Supplementary',
		'To', 'Type', 'Type-Species','That','Title','Tautonymy','Taken','Thereto','Trace','Treated','Thereunder',
        'Used','Usage','Thereby','Upon', 'Unavailable','Und',
        'Validated', 'Validation','Validate','Vary','Valid', 'Var.','Validating','Var','Various',
		'With', 'Works','Which', 'Was','Work',
    
         'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven', 'Eight', 'Ten',
         'Twelve', 'Fifteen','Nineteen',
         'Twenty-one','Twenty-two',
         'Thirty-four',
         'Fifty-five', 'Fifty-Seven', 'Eighty-Six','Ninety-Eight','Ninety-eight','Twenty-nine','Fifty-two','Seventy-eight','Seventy',
         'Hundred', 'Seventy-four'
	);
	
	$text = $str;	
	
	$text = mb_convert_case($text, MB_CASE_TITLE, "UTF-8");
	$text = preg_replace('/\n/u', ' ', $text);
	$text = preg_replace('/\s\s+/u', ' ', $text);
	$text = preg_replace('/\)\s+;/u', ');', $text);
	$text = preg_replace('/\)\s+:/u', '):', $text);
	$text = preg_replace('/Lepidop-Tera/u', 'Lepidoptera', $text);
	$text = preg_replace('/Forsskal/u', 'Forsskål', $text);
	$text = preg_replace('/Stal/u', 'Stål', $text);
	$text = preg_replace('/\s+$/u', '', $text);
	
	// OCR errors
	$text = preg_replace('/\{/u', '(', $text);
	
	$text = preg_replace('/Pubhshed/u', 'Published', $text);
	$text = preg_replace('/pubhshed/u', 'published', $text);
	$text = preg_replace('/Hiibner/u', 'Hübner', $text);
	$text = preg_replace('/Ruhng/u', 'Ruling', $text);
	$text = preg_replace('/MoUusca/u', 'Mollusca', $text);
	
	
	
	
	
	
	foreach ($terms as $term)
	{
		$text = preg_replace('/(\b)' . $term . '(\b)/ui', '$1' . strtolower($term) . '$2', $text);
	}			
	$text = preg_replace('/^(Opinion \d+)\./u', '$1', $text);
	$text = preg_replace('/^(Opinion \d+):/u', '$1', $text);

	$text = preg_replace('/^(Direction \d+)\./u', '$1', $text);

	
	// Ensure opinion starts with capital letters
	if (preg_match('/^(?<first>Opinion\s+\d+)\s+(?<letter>[a-z])(?<rest>.*)$/u', $text, $m))
	{
		$text = $m['first'] . ' ' . strtoupper($m['letter']) . $m['rest'];
	}
	if (preg_match('/^(?<first>Direction\s+\d+)\s+(?<letter>[a-z])(?<rest>.*)$/u', $text, $m))
	{
		$text = $m['first'] . ' ' . strtoupper($m['letter']) . $m['rest'];
	}
	
	// Fix things likely to be messed up after changing case
	$text = preg_replace('/D\'orbigny/u', 'd\'Orbigny', $text);
	$text = preg_replace('/L\'egypte/u', 'L\'Egypte', $text);
	$text = preg_replace('/\(a\./u', '(A.', $text);
	$text = preg_replace('/\s+a\./u', ' A.', $text);
	$text = preg_replace('/\s+d\./u', ' D. ', $text);
	$text = preg_replace('/\s+h\./u', ' H. ', $text);

	$text = preg_replace('/de man/u', 'de Man', $text);
	

	$text = preg_replace('/of generic names/ui', 'of Generic Names', $text);
	$text = preg_replace('/of zoological names/ui', 'of Zoological Names', $text);
	$text = preg_replace('/case of/ui', 'case of', $text);
	$text = preg_replace('/ vs\. /ui', ' vs. ', $text);

	$text = preg_replace('/of rejected/ui', 'of Rejected', $text);
	$text = preg_replace('/of family-group names/ui', 'of Family-group Names', $text);
	$text = preg_replace('/of family group names/ui', 'of Family Group Names', $text);
	$text = preg_replace('/Invalid specific names/ui', 'Invalid Specific Names', $text);
	$text = preg_replace('/Works Approved/ui', 'Works Approved', $text);
	$text = preg_replace('/of specific names/ui', 'of Specific Names', $text);
	$text = preg_replace('/Case May/ui', 'case may', $text);
	$text = preg_replace('/A Genus/ui', 'a genus', $text);
	$text = preg_replace('/The Genus/ui', 'the genus', $text);
	$text = preg_replace('/Genus Concerned/ui', 'genus concerned', $text);
	$text = preg_replace('/Nominal Genus/ui', 'nominal genus', $text);


	
	// simple species names
	/*$text = preg_replace('/([A-Z][a-z]+) ([A-Z][a-z]+) ([A-Z][a-z]+), ([0-9]{4})/u', 
		'$1' . ' ' . strtolower('$2') . ' $3, $4',
		$text);*/
	if (preg_match_all('/([A-Z][a-z]+) ([A-Z][a-z]+) (?<author>(([A-Z]\.\s+)?[A-Z][a-z]+)(\s+&\s+\w+)?), (?<year>[0-9]{4})/u', $text, $m))
	{
		//print_r($m);
		
		$n = count($m[0]);
		for ($i = 0; $i < $n; $i++)
		{
			$name = $m[1][$i] . ' ' . strtolower($m[2][$i]) . ' ' . $m['author'][$i] . ', ' . $m['year'][$i];
			
			//echo $name;
			$text = preg_replace('/' . $m[0][$i] . '/u', $name, $text);
		}
	}
	
	//  nominal species Cancer Oculatus
	if (preg_match('/nominal species\s+(?<genus>[A-Z][a-z]+)\s+(?<species>[A-Z][a-z]+)/u', $text, $m))
	{
		$name = 'nominal species ' . $m['genus'] . ' ' . strtolower($m['species']);
		$text = preg_replace('/' . $m[0] . '/u', $name, $text);
	}
	
	if (preg_match('/binomen (?<genus>[A-Z][a-z]+)\s+(?<species>[A-Z][a-z]+)/u', $text, $m))
	{
		$name = 'binomen ' . $m['genus'] . ' ' . strtolower($m['species']);
		$text = preg_replace('/' . $m[0] . '/u', $name, $text);
	}

	if (preg_match('/combination (?<genus>[A-Z][a-z]+)\s+(?<species>[A-Z][a-z]+)/u', $text, $m))
	{
		$name = 'combination ' . $m['genus'] . ' ' . strtolower($m['species']);
		$text = preg_replace('/' . $m[0] . '/u', $name, $text);
	}
	
	
	// specific name Gemmascens
	if (preg_match('/specific name\s+(?<species>[A-Z][a-z]+)/u', $text, $m))
	{
		$name = 'specific name ' . strtolower($m['species']);
		$text = preg_replace('/' . $m[0] . '/u', $name, $text);
	}
		
	return 	$text;
}

?>