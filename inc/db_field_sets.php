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

function & dbGetFieldSets(& $dbo,$field_set = null) {
	
	
	$field_sets = array ();
	
	if ($field_set !== null){
		$whereString = ' WHERE field_set = \''.dbGetFieldSetId($field_set).'\'';
	}

	$AllFields = dbGetFields($dbo);

	$sql = 'SELECT * FROM '.$dbo->table['subscriber_field_sets'].$whereString.' ORDER BY field_set_order';	
	while ($row = $dbo->getRows($sql)) {
		$a = array ();
		$a['field_set'] = $row['field_set'];
		$a['description'] = $row['field_set_description'];
		$a['fields'] = array();;
		$field_sets[$row['field_set']] = $a;
	}
	
	foreach ($field_sets as $field_set_id => $field_set){
		$field_ids = dbGetFieldSetFields($dbo,$field_set_id);
		foreach ($field_ids as $field_id){
			if (array_key_exists($field_id,$AllFields)){
				$field_sets[$field_set_id]['fields'][$field_id] = $AllFields[$field_id];
			}
		}
	}
	
	return (!empty($field_sets)) ? $field_sets : array();
}

function dbGetFieldSetFields(& $dbo, $fieldSetID){
	$sql = 'SELECT field_id FROM '.$dbo->table['subscriber_fields'].' WHERE field_set=\''.$fieldSetID.'\' ORDER BY field_ordering';

	$field_ids = array();
	while ($row = $dbo->getRows($sql)) {
		$field_ids[] = $row['field_id'];
	}
	
	return $field_ids;
}

// returns a field Set ID based off name
function dbGetFieldSetId($name) {
	global $dbo;
	$sql = 'SELECT field_set FROM '.$dbo->table['subscriber_field_sets'].' WHERE field_set_description=\''.$name.'\'';
	return ($dbo->query($sql, 0));
}

// dbfieldSetCheck: <bool> - Returns true if a name/id field exists
function dbFieldSetCheck(& $dbo, $fieldId) {

	// determine if we're to check for name or id -- note field names CANNOT be numeric
	if (is_numeric($fieldId))
		$sql = 'SELECT count(field_set) FROM '.$dbo->table['subscriber_field_sets'].' WHERE field_set=\''.$fieldId.'\'';
	else
		$sql = 'SELECT count(field_set) FROM '.$dbo->table['subscriber_field_sets'].' WHERE field_set_description=\''.$fieldId.'\'';

	return ($dbo->query($sql, 0)) ? true : false;
}

// dbfieldSetAdd: <bool> - Returns true if a field of passed 'fieldname' was added
function dbFieldSetAdd(& $dbo, $fieldSetDescription) {

	// field NAMES CANNOT BE NUMERIC, or duplicate
	if (is_numeric($fieldSetDescription) || dbFieldSetCheck($dbo, $fieldSetDescription))
		return false;

	// get the last ordering
	$sql = 'SELECT field_set_order FROM '.$dbo->table['subscriber_field_sets'].' ORDER BY field_set_order DESC';
	$order = $dbo->query($sql, 0) + 1;
	
	$sql = 'INSERT INTO '.$dbo->table['subscriber_field_sets'].' SET field_set_description=\''.$fieldSetDescription.'\', field_set_order='.$order.'';
	return $dbo->affected($sql);
}

// dbFieldSetDelete: <bool> - Returns true if the passed fieldSetId was deleted, false if nothing was.
function dbFieldSetDelete(& $dbo, $fieldSetId) {

	// return false if a bad field was passed.
	if (!dbFieldSetCheck($dbo, $fieldSetId))
		return false;
		
	// First, get all of the fields in the Field Set
	$fields = dbGetFieldSetFields($dbo,$fieldSetId);
	
	foreach ($fields as $field_id){
		dbFieldDelete($dbo,$field_id);
	}

	// delete field set
	$sql = 'DELETE FROM '.$dbo->table['subscriber_field_sets'].' WHERE field_set=\''.$fieldSetId.'\'';
	return $dbo->affected($sql);
}
?>