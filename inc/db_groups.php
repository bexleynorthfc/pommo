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

// TODO implement tallyCache
// TODO redo Group object

/** 
* Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

/** Database Helper file for group management */

// dbGetGroups: <array> - Returns an array of groups. The array key
//  is the group_id, and it's value is group name. If where is given, a specific group is returned
function & dbGetGroups(& $dbo, $where = NULL) {

	$whereStr = '';
	if (is_numeric($where)){
		$whereStr = ' WHERE group_id=\''.$where.'\'';
	}
	elseif($where != ''){
		$whereStr = ' WHERE '.$where;
	}

	if (function_exists('apply_filters')){
		$whereStr = apply_filters('pommo_dbGetGroups_whereStr',$whereStr);
	}

	$groups = array ();

	$sql = 'SELECT group_id, group_name FROM '.$dbo->table['groups'].$whereStr.' ORDER BY group_name';
	if (function_exists('apply_filters')){
		$sql = apply_filters('pommo_dbGetGroups_sql',$sql);
	}
	while ($row = $dbo->getRows($sql, TRUE)) {
		$groups[$row[0]] = $row[1];
	}
	return $groups;
}

// dbGroupCheck: <bool> - Returns true if group name or Id exists in database. 
function dbGroupCheck(& $dbo, $groupId) {

	// determine if we're to check for name or id -- note group names CANNOT be numeric or "all"
	if ($groupId == 'all')
		return true;
	elseif (is_numeric($groupId)) $sql = 'SELECT count(group_id) FROM '.$dbo->table['groups'].' WHERE group_id=\''.$groupId.'\'';
	else
		$sql = 'SELECT count(group_id) FROM '.$dbo->table['groups'].' WHERE group_name=\''.$groupId.'\'';

	return ($dbo->query($sql, 0)) ? true : false;
}

// dbGroupName: <str> - Returns the name of a group given an ID
function dbGroupName(& $dbo, $groupId) {
	if ($groupId == 'all'){
		if (defined('bm_SubscriberWord')){
			return 'All '.bm_SubscriberWord.'s';
		}
		else{
			return 'All Subscribers';
		}
	}
	
	$sql = 'SELECT group_name FROM '.$dbo->table['groups'].' WHERE group_id=\''.$groupId.'\'';
	return $dbo->query($sql, 0);
}

// dbGroupAdd: <bool> - Returns true if a group of passed 'groupname' was added
function dbGroupAdd(& $dbo, $groupName) {

	// GROUP NAMES CANNOT BE NUMERIC, or duplicate, or "all"
	if (is_numeric($groupName) || dbGroupCheck($dbo, $groupName))
		return false;

	$sql = 'INSERT INTO '.$dbo->table['groups'].' SET group_name=\''.$groupName.'\'';
	if (function_exists('apply_filters')){
		$sql = apply_filters('pommo_dbGroupAdd_sql',$sql);
	}
	return $dbo->affected($sql);
}

// dbGroupDelete: <bool> - Returns true if the passed groupId was deleted, false if nothing was.
function dbGroupDelete(& $dbo, $groupId) {

	// return false if a bad group was passed.
	if (!dbGroupCheck($dbo, $groupId))
		return false;

	// delete criteria related to this group...
	$sql = 'DELETE FROM '.$dbo->table['groups_criteria'].' WHERE group_id=\''.$groupId.'\'';
	$dbo->query($sql);
	$sql = 'DELETE FROM '.$dbo->table['groups_criteria'].' WHERE logic=\'is_in\' OR logic=\'not_in\' AND value=\''.$groupId.'\'';
	$dbo->query($sql);

	// delete group
	$sql = 'DELETE FROM '.$dbo->table['groups'].' WHERE group_id=\''.$groupId.'\'';
	return $dbo->affected($sql);
}

// dbGroupUpdateName: <bool> - Returns true if a group's name was updated
function dbGroupUpdateName(& $dbo, $group_id, $groupName) {

	// GROUP NAMES CANNOT BE NUMERIC, or Duplicate.
	if (is_numeric($groupName) || dbGroupCheck($dbo, $groupName))
		return false;

	$sql = 'UPDATE '.$dbo->table['groups'].' SET group_name=\''.$groupName.'\' WHERE group_id=\''.$group_id.'\' LIMIT 1';
	return $dbo->affected($sql);
}

// <array> : Returns an array of criteria filters. Array's key is criteria_id and it points
//  to an array holding criteria group_id, field_id, logic, and value.
//  if group_id is given, only criteria pertaining to group_id will be returned.
//  if criteria_id is given, only that criteria will be returned
function & dbGetGroupFilter(& $dbo, $group_id = NULL, $criteria_id = NULL) {

	$whereSql = array ();

	// check if parameters are provided that will impact the SELECT statement
	if (is_numeric($group_id))
		$whereSql[] = 'group_id=\''.$group_id.'\'';
	if (is_numeric($criteria_id))
		$whereSql[] = 'criteria_id=\''.$criteria_id.'\'';

	// initialize the whereStr (to be appended to the SELECT statement)
	$whereStr = '';
	$whereSqlSize = count($whereSql);

	// if WHERE conditions exist, append them to whereStr
	if ($whereSqlSize >= 1) {
		$whereStr = ' WHERE ';
		$i = 0;
		foreach (array_keys($whereSql) as $key) {
			$i ++;
			$whereStr .= $whereSql[$key];
			if ($i < $whereSqlSize)
				$whereStr .= ' AND ';
		}
	}

	// this array will be returned
	$returnArray = array ();

	$sql = 'SELECT * FROM '.$dbo->table['groups_criteria'].$whereStr;
	while ($row = $dbo->getRows($sql)) {
		$a = array ();
		$a['group_id'] = $row['group_id'];
		$a['field_id'] = $row['field_id'];
		$a['logic'] = $row['logic'];
		$a['value'] = $row['value'];
		$returnArray[$row['criteria_id']] = $a;
	}
	return $returnArray;
}

// dbGroupFilterDel: <bool> - Returns true [# of records affected] if a filtering criteria was removed from the database, false (0) if none.
function dbGroupFilterDel(& $dbo, $criteria_id) {
	// verify criteria is_numeric
	if (!is_numeric($criteria_id))
		return false;
	$sql = 'DELETE FROM '.$dbo->table['groups_criteria'].' WHERE criteria_id=\''.$criteria_id.'\' LIMIT 1';
	return $dbo->affected($sql);
}

// dbGroupFilterVerify: <bool> - Returns true if a filter addition/update proves to be sane.
function dbGroupFilterVerify(& $dbo, & $group_id, & $field_id, & $logic, & $value) {

        // Trevor Mills - removing this logic - we'll allow everything, regardless of sanity
        return true;
        

	$oppLogic = NULL;
	switch ($logic) {
		case 'is_in' :
		case 'not_in' :
			// verify target group doesn't point to this one
			/* Rempoved -- loops are handled in the code that generates a groups SQL 'WHERE' logic.
			$sql = 'SELECT count(criteria_id) FROM groups_criteria WHERE group_id=\''.$value.'\' AND value=\''.$group_id.'\' AND (logic=\'is_in\' OR logic=\'not_in\')';
			if ($dbo->query($sql, 0))
				return false; */
			// verify that this group does not already reference the target
			$sql = 'SELECT count(criteria_id) FROM '.$dbo->table['groups_criteria'].' WHERE group_id=\''.$group_id.'\' AND value=\''.$value.'\' AND (logic=\'is_in\' OR logic=\'not_in\')';
			break;
			// set opposite logic, verify that a criteria is not trying to do the opposite as this one	
		case 'is_equal' :
		case 'not_equal' :
			$oppLogic = 'is_equal';
			if ($oppLogic == $logic)
				$oppLogic = 'not_equal';
			break;
		case 'is_more' :
		case 'is_less' :
			$oppLogic = 'is_more';
			if ($oppLogic == $logic)
				$oppLogic = 'is_less';
			break;
		case 'is_true' :
		case 'not_true' :
			$oppLogic = 'is_true';
			if ($oppLogic == $logic)
				$oppLogic = 'not_true';
			break;
                case 'contains' :
                case 'does_not_contain' :
			$oppLogic = 'contains';
			if ($oppLogic == $logic)
				$oppLogic = 'does_not_contain';
			break;
		default :
			return false;
	}
	if (!empty ($oppLogic))
		$sql = 'SELECT count(criteria_id) FROM '.$dbo->table['groups_criteria'].' WHERE group_id=\''.$group_id.'\' AND field_id=\''.$field_id.'\' AND logic=\''.$oppLogic.'\'';
	if ($dbo->query($sql, 0))
		return false;

	return true;
}

// dbGroupFilterUpdate: <bool> - Returns true if a criteria's value has been updated'
function dbGroupFilterUpdate(& $dbo, $criteria_id, $value = NULL) {

		// get info -- 
	$sql = 'SELECT group_id, field_id, logic FROM '.$dbo->table['groups_criteria'].' WHERE criteria_id=\''.$criteria_id.'\'';
	$row = mysql_fetch_row($dbo->query($sql));

	if (!$row || !dbGroupFilterVerify($dbo, $row['0'], $row['1'], $row['2'], $value))
		return false;

	$value = addslashes($value);
	$sql = 'UPDATE '.$dbo->table['groups_criteria'].' SET value=\''.$value.'\' WHERE criteria_id=\''.$criteria_id.'\'';
	return $dbo->affected($sql);
}

// dbGroupFilterAdd: <bool> - Returns true if a filtering criteria was added to the database
function dbGroupFilterAdd(& $dbo, $group_id, $field_id, $logic, $value = NULL) {

	if (!dbGroupFilterVerify($dbo, $group_id, $field_id, $logic, $value))
		return false;

	if(is_array($value)) {
		require_once (bm_baseDir . '/inc/lib.txt.php');
		$value = array2csv($value);
	}
	$value = addslashes($value);
	$sql = 'INSERT INTO '.$dbo->table['groups_criteria'].' (group_id, field_id, logic, value) VALUES (\''.$group_id.'\', \''.$field_id.'\', \''.$logic.'\', \''.$value.'\')';
	return $dbo->affected($sql);
}

// dbGroupTally: <int> - Returns the # of subscribers in a group
function dbGroupTally(& $dbo, $group_id, $table = 'subscribers') {
	if (!dbGroupCheck($dbo, $group_id))
		return false;
		
	if ($group_id == 'all') {
		$sql = 'SELECT count('.$table.'_id) FROM '.$dbo->table[$table];
		return $dbo->query($sql,0);
	}
		
	require_once (bm_baseDir.'/inc/db_sqlgen.php');
	return dbGetGroupSubscribers($dbo, $table, $group_id, 'count');
	}
        
// The functions to do with FilterLogic were added by Trevor Mills
// They allow a user to choose whether a group requires ALL filters to be matched
// or ANY filter to be matched

// dbGroupSetFilterLogic: <bool> returns true on success
function dbGroupSetFilterLogic(& $dbo, $group_id, $filter_logic = 'all'){
        // filter_logic must be 'all' or 'any'
        $valid_logics = array('all','any');
        $filter_logic = strtolower($filter_logic);
        if ($filter_logic != 'all' && $filter_logic != 'any') 
		return false;

	$sql = 'UPDATE '.$dbo->table['groups'].' SET group_filter_logic=\''.$filter_logic.'\' WHERE group_id=\''.$group_id.'\' LIMIT 1';
	return $dbo->affected($sql);

        }
        
// dbGroupGetFilterLogic: <str> - Returns the Filter Logic to use ('all' or 'any')
function dbGroupGetFilterLogic(& $dbo, $groupId) {
	$sql = 'SELECT group_filter_logic FROM '.$dbo->table['groups'].' WHERE group_id=\''.$groupId.'\'';
        
        $filter_logic = $dbo->query($sql, 0);
        // default to 'all'
        if (!$filter_logic)
                return 'all';
        else
                return $filter_logic;
        }
        


?>