<?php
/** [BEGIN HEADER] **
 * COPYRIGHT: (c) 2005 Brice Burgess / All Rights Reserved    
 * LICENSE: http://www.gnu.org/copyleft.html GNU/GPL 
 * AUTHOR: Brice Burgess <bhb@iceburg.net>
 * SOURCE: http://pommo.sourceforge.net/
 *
 *  :: RESTRICTIONS ::
 *  1. This header must accompany all portions of code contained within.
 *  2. You must notify the above author of modifications to contents within.
 * 
 ** [END HEADER]**/

/** 
* Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

// TODO -> Rewrite import process.... 

require_once (bm_baseDir.'/inc/lib.txt.php');

// Reads a CSV file and returns an array. Returns false if the file is invalid.
//  Validation -> 1 email address per line, # of cells not to exceed # of fields by 5
//  Output array {  array ('lineWithMostFields' => 0, 'emailField' => '', 'csvFile' => array([field1],[field2],[...]), 'fieldAssign' => array(...) { [0]=>  string(5) "email" [1]=>  string(2) "13" ...} }
function & csvPrepareFile(& $uploadFile, $headerRow = false) {

	global $logger;
	global $dbo;
	global $poMMo;
	
	
	// set maximum fields / line based off # of fields
	$sql = 'SELECT COUNT(field_id) FROM '.$dbo->table['subscriber_fields'];
	$maxFields = $dbo->query($sql, 0) + 5;

	// the most fields the parser encounters / line
	$mostFields = 0;

	// the array which will be returned
	$outArray = array ('lineWithMostFields' => 0, 'emailField' => '', 'idField' => '', 'csvFile' => array(), 'fieldAssign' => array());
        
        if ($headerRow){         
                require_once (bm_baseDir.'/inc/db_fields.php');
                $fields = dbGetFields($dbo);
                $validFields = array();
                foreach (array_keys($fields) as $field_id){
                        $validFields[$field_id] = $fields[$field_id]['name'];
                }
        }

	// read the file into an array
	$parseFile = file($uploadFile);
	if (count($parseFile) == 1){
	    // I was running into problems when uploading on a MAC (or using files created by a MAC)
	    // The problem can be fixed by setting the php.ini directive auto_detect_line_endings to on
	    // but since I can't guarantee that would be set on a server, I'm going to auto split based on \n
	    $tmp = $parseFile[0];
	    $parseFile = explode("\r",$tmp);
	}
	    
        
	$fail = 0;
	foreach ($parseFile as $line_num => $line) {
            if ($line_num === 0 && $headerRow){         
                $fields = @ quotesplit($line);
                foreach($fields as $fieldNumber => $field){
                        if ($field == 'email'){
                                $outArray['emailField'] = $fieldNumber;
                        }
                        elseif ($field == 'subscriber_id'){
                                $outArray['idField'] = $fieldNumber;
                        }
                        elseif ($field == 'date'){
                                // ignore
                        }
                        elseif ($key = array_search($field,$validFields)){
                                $outArray['fieldAssign'][$key] = $fieldNumber;
                        }
                        else{
                                $fail = true;
                                $logger->addMsg(sprintf(_T('No field with the name \'%s\' exists.  File cannot be imported.'),$field));
                                break;
                        }
                }
            }
            else{
		if ($fail > 3) {
			$logger->addMsg(_T('Maximum failures reached. CSV processing aborted.'));
			break;
		}
                // Allow line breaks to appear (this should only affect 'bigtext' fields)
                // Note, they get properly escaped later in the import process.  this just stores
                // them in a benign session variable
                $line = str_replace('\\n',"\n",$line);
                $line = str_replace('\\r',"\r",$line);
                
		$fields = @ quotesplit($line);
		$numFields = count($fields);

		// check to see if any fields were read in
		if (!$numFields || $numFields < 1) {
			$logger->addMsg(sprintf(_T('Line #%s could not be processed.'),$line_num +1));
			$fail++;
			continue; // skip this line, as it has failed sanity check.
		}

		// check to see if this line exceeded the maximum allowed fields
		if ($numFields > $maxFields) {
			$logger->addMsg(sprintf(_T('Line #%s had too many fields.'),$line_num +1));
			$fail++;
			continue; // skip this line, as it has failed sanity check.
		}
                
                // If there is a 'subscriber_id' field, then check and make sure the subscriber exists
                if ($outArray['idField'] !== "" && $fields[$outArray['idField']] != ""){
	                require_once (bm_baseDir.'/inc/db_subscribers.php');
                        $subscriber = dbGetSubscriber($dbo,mysql_real_escape_string($fields[$outArray['idField']]));
                        if (!count($subscriber)){
				$logger->addMsg(sprintf(_T('Line #%s has an unknown subscriber id (%s).'),$line_num +1,$fields[$outArray['idField']]));
                                $fail++;
			        continue; // skip this line, as it has failed sanity check.
                        }
                        elseif(count($subscriber) > 1){
				$logger->addMsg(sprintf(_T('Line #%s has an ambiguous subscriber id (%s).'),$line_num +1,$fields[$outArray['idField']]));
                                $fail++;
			        continue; // skip this line, as it has failed sanity check.
                        }
                }

		$emailCount = 0;

		// travel through the fields, performing any validation
		foreach ($fields as $key => $field) {
			if (isEmail($field) && $outArray['emailField'] === '') {
				if (!empty($outArray['emailField']) && $key != $outArray['emailField']) {
					$logger->addMsg(sprintf(_T('Line #%s had email address in a different field(cell).'),$line_num +1));
					$fail++;
					continue;
				}
				$outArray['emailField'] = $key;
				$emailCount ++;
			}
		}

		if ($emailCount > 1) {
			$logger->addMsg(sprintf(_T('Line #%s had more than one email address.'),$line_num +1));
			$fail++;
			continue; // skip this line, as it has failed sanity check.
		}

		if ($emailCount == 0) {
                        if (!defined('bm_BlankEmail')){
			        $logger->addMsg(sprintf(_T('Line #%s had no email address.'),$line_num +1));
			        $fail++;
			        continue; // skip this line, as it has failed sanity check.	
                        }
		}

		// check to see if this line has the most fields we've seen so far
		if ($numFields > $mostFields) {
			$mostFields = $numFields;
			$outArray['lineWithMostFields'] = $line_num;
		}

		$outArray['csvFile'][$line_num] = $fields;
             }
	}
        
	// return false if there were errors
	if ($fail)
		return false;

	return $outArray;
}

// csvPrepareImport: <array> returns an array of dbGetSubscriber style subscribers to import. 
// The array consists of 2 arrays, 'valid' and 'invalid'. If a subscriber is in 'invalid', they will be flagged to
//  update their records.

// fields: dbGetFields 
// csvFile: 2D array of fields [lines of file]
// fieldAssign - array of field assignments; e.g. array(2) { [0]=>  string(5) "email" [1]=>  string(2) "13" } 
function csvPrepareImport(& $fields, & $csvFile, $fieldAssign) {
	
	global $poMMo;
	global $dbo;
	global $logger;
	require_once (bm_baseDir.'/inc/db_subscribers.php');

	$outArray = array ('valid' => array (), 'invalid' => array (), 'duplicate' => array ());
        
	// array of required fields
	$requiredArray = array ();
	foreach (array_keys($fields) as $field_id){
		if ($fields[$field_id]['required'] == 'on')
			$requiredArray[$field_id] = $fields[$field_id]['name'];
        }

	// find the field # holding the email address or the field # holding the subscriber_id
        // We allow the subscriber_id to identify the imported record to allow for the use case
        // of an admin exporting data to Excel, editting it there and re-importing it, including
        // subscribers with no email addresses.  The idField would take precedence if present
	foreach (array_keys($fieldAssign) as $field_num) {
		if ($fieldAssign[$field_num] == 'email') {
			$emailField = $field_num;
		}
		if ($fieldAssign[$field_num] == 'subscriber_id') {
			$idField = $field_num;
		}
	}

	// go through each row of the csvFile, and validate the entries
        $EmailsAlreadyImported = array();
	foreach (array_keys($csvFile) as $line) {

		$entries = & $csvFile[$line];

		// begin the subscriber for this row
		$subscriber = array ('data' => array ());
		$valid = TRUE;

		// array of required fields.
		$required = $requiredArray;
                
                if (defined('bm_BlankEmail') && $entries[$emailField] == ""){
                        $entries[$emailField] = bm_BlankEmail;
                }

                // Deal with it separately depending on whether or not idField is present
                if (isset($idField) && $idField !== "" && $entries[$idField] != ""){
                        // Search for a subscriber with that ID
                        $subscriber = dbGetSubscriber($dbo,mysql_real_escape_string($entries[$idField]));
                        $count = count($subscriber);
                        
                        if (!$count or $count > 1){
        		        $logger->addMsg(sprintf(_T('Subscriber ID on line %1$s could not be found found.  This row will not be imported'),$line + 1));			
                                continue; // Go on to the next row
                        }
                        else{
                                $subscriber = array_pop($subscriber);
                                if ($subscriber['email'] != $entries[$emailField]){
                                        $subscriber['newEmail'] = $entries[$emailField];
                                }
                        }
                }
                else{
        		if (!isDupeEmail($dbo, $entries[$emailField])){
                                $tmp_email = $entries[$emailField];
                                if (in_array(strtolower($tmp_email),$EmailsAlreadyImported)){
        		                $logger->addMsg(sprintf(_T('Subscriber on line %1$s has an email (%2$s) that was found earlier in the imported file and will be ignored.'),$line + 1,$entries[$emailField]));			
                                        continue; // Go on to the next row
                                }
                                if(!defined('bm_BlankEmail') || $tmp_email !== bm_BlankEmail){
                                        $EmailsAlreadyImported[] = strtolower($tmp_email);
                                }
        			$subscriber['email'] = $tmp_email;
                        }
        		else {
                                $subscriber = dbGetSubscriber($dbo,mysql_real_escape_string($entries[$emailField]));
                                if (count($subscriber) == 1){
        			        $outArray['duplicate'][] = $entries[$emailField].' (line '.($line+1).')';
                                        $subscriber = array_pop($subscriber);
                                }
                                else{
        			        $outArray['duplicate'][] = $entries[$emailField].' on line '.($line+1).' is ambiguous (multiple subscribers exist with that email) and was therefore ignored.';
                                        continue;
                                }
                                        
        		}
                }

        	// go through each field in a row
        	foreach ($entries as $field_num => $value) {
        
        		if ($fieldAssign[$field_num] == 'ignore' || $field_num == $emailField || $field_num == $idField)
        			continue;
        
        		// trim the value of whitespace
        		$value = trim($value);
        
        		// if the value is empty, skip. Required fields will be checked below
        		if (empty ($value))
        			continue;
        
                        // If the imported value has a '++' at the beginning of it, then 
                        // the value is to be APPENDED to the existing value (if one exists)
                        // This is only used for 'multiplemultiple' or 'bigtext' and allows someone 
                        // to import without destroying the current value.  
                        if (strpos($value,'++') === 0){
                                $value = substr($value,2);
                                $append = true;
                        }
                        else{
                                $append = false;
                        }
                                
        		// assign the field_id to this field
        		$field_id = $fieldAssign[$field_num];
        		$field = $fields[$field_id];
        
        		// validate this field
        		switch ($field['type']) {
        			case 'checkbox' :
        				if ($value == 'on' || $value == 'ON' || $value == 'checked' || $value == 'CHECKED' || $value = 'yes' || $value == 'YES')
        					$subscriber['data'][$field_id] = 'on';
        				break;
        			case 'multiple' :
        				// verify the input matches a selection (for data congruency)
        				$options = $field['options'];
        				if (in_array($value, $options)) {
        					$subscriber['data'][$field_id] = $value;
        				}
        				else {
        					$logger->addMsg(sprintf(_T('Subscriber on line %1$s has an unknown option (%2$s) for field %3$s'),$line + 1,$value, $field['name']));
        					$valid = FALSE;
        				}
        				break;
                                case 'multiplemultiple' :
        				// verify the input matches a selection (for data congruency)
        				$options = $field['options'];
                                        $values =  quotesplit($value);
                                        $valid_values = array();
                                        if ($append && $subscriber['data'][$field_id] != ""){
                                                $current_data = explode(',',$subscriber['data'][$field_id]);
                                        }
                                        else{
                                                $current_data = array();
                                        }
                                        foreach ($values as $value){
                				if (in_array($value, $options)) {
                                                        $valid_values[] = $value;
                				}
                				else {
                					$logger->addMsg(sprintf(_T('Subscriber on line %1$s has an unknown option (%2$s) for field %3$s'),$line + 1,$value, $field['name']));
                					$valid = FALSE;
                				}
                                        }
                			$subscriber['data'][$field_id] = implode(',',array_unique(array_merge($current_data,$valid_values)));
                                        
        				break;
        			case 'date' : // validate if input is a date
        				$date = strtotime($value);
        				if ($date)
        					$subscriber['data'][$field_id] = $date;
        				else {
        					$logger->addMsg(sprintf(_T('Subscriber on line %1$s has an invalid date (%2$s) for field %3$s'),$line + 1,$value, $field['name']));
        					$valid = FALSE;
        				}
        				break;
        			case 'text' :
        				$subscriber['data'][$field_id] = $value;
        				break;
        			case 'bigtext' :
                                        // Allow linebreaks to be preserved for 'bigtext'
                                        if ($append){
                                                $subscriber['data'][$field_id].= $subscriber['bigdata'][$field_id] . "\n\n" . $value;
                                        }
                                        else{
                                                $subscriber['data'][$field_id] = $value;
                                        }
        				break;
        			case 'number' :
        				if (is_numeric($value))
        					$subscriber['data'][$field_id] = $value;
        				else {
        					$logger->addMsg(sprintf(_T('Subscriber on line %1$s has a non number (%2$s) for field %3$s'),$line + 1,$value, $field['name']));
        					$valid = FALSE;
        				}
        				break;
        			default :
        				die('Unknown Type in Import Process');
        		}
        
        		// tick off this field from the required fields if it was required
        		if (isset ($required[$field_id]))
        			unset ($required[$field_id]);
        	}
        
                $ignoreCheckBecauseNotRequired = (defined('bm_importAllowBlankRequired') && bm_importAllowBlankRequired);
        	if (!empty ($required) && !$ignoreCheckBecauseNotRequired) {
        		foreach (array_keys($required) as $field_id)
        		$logger->addMsg(sprintf(_T('Subscriber on line %1$s has a empty required field (%2$s)'),$line + 1,$fields[$field_id]['name']));			
        		$valid = FALSE;
        	}
        
        	if ($valid)
        		$outArray['valid'][] = $subscriber;
        	else
        		$outArray['invalid'][] = $subscriber;
        }
        return $outArray;
}
?>