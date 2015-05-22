String.prototype.toTitleCase = function() {
  var i, j, str, lowers, uppers;
  str = this.replace(/([^\W_]+[^\s-]*) */g, function(txt) {
    return txt.charAt(0).toUpperCase() + txt.substr(1).toLowerCase();
  });

  // Certain minor words should be left lowercase unless 
  // they are the first or last words in the string
  lowers = [
  		'A', 'Accustomed', 'And', 'As', 'Assigned',
  		'By','Be',
        'Certain',
		'Date', 'Des', 
		'Emended', 'Et',
		'For', 'From',
        'Given',
		'In',
        'Known',
        'Not',
		'Of', 
        'Portions',
        'Sense',
		'The', 'To', 
		'Under',

		'Added', 'Addition','Associated','Accordance','Accepted','Availability','Author',
        'Binomen',
        'Conserved','Conservation','Currently','Confirmed',
		'Designation','Designations','Designated',
        'Emendations', 'Emendations','Existing','Entry','Entries',
        'Family',
        'Class',
        'Gen','Group',
		'Harmony', 'Holotype',
        'Interpretation','Its',
		'Lectotype','Lectotypes',
        'Matters',
		'Name', 'Names', 'Neotype','Nominal',
		'On','Order', 'Over',
        'Placed','Published','Publication','Pamphlet','Precedence',
        'Ruling','Ruled',
		'Species','Suppressed', 'Suppression','Specific','Spelling','Specimen','Status',
		'To', 'Type', 'Type-Species','That',
        'Used','Usage',
        'Validated', 'Validation','Validate','Vary',
		'With', 'Works','Which', 'Was','Work',
    
         'One', 'Two', 'Three', 'Four', 'Five', 'Six', 'Seven'
		];
		
  for (i = 0, j = lowers.length; i < j; i++)
    str = str.replace(new RegExp('\\s' + lowers[i] + '\\s', 'g'), 
      function(txt) {
        return txt.toLowerCase();
      });
 

  // Certain words such as initialisms or acronyms should be left uppercase
  uppers = ['Id', 'Tv'];
  for (i = 0, j = uppers.length; i < j; i++)
    str = str.replace(new RegExp('\\b' + uppers[i] + '\\b', 'g'), 
      uppers[i].toUpperCase());

  return str;
}


function NORMALISE(input) {
  var s = input;
  s = input.toTitleCase();
  // Clean up punctuation
  s = s.replace(/^Opinion\s+(\d+)\./, 'Opinion $1');
   s = s.replace(/^Direction\s+(\d+)\./, 'Direction $1');
  s = s.replace(/\)\s+:/, '):');
  s = s.replace(/\)\s+;/, '):');
  s = s.replace(/\s\s+/, ' ');
  s = s.replace(/\n/g, ' ');
  // Ensure first letter of word after Opinion is capitalised
  
  return s;
}
