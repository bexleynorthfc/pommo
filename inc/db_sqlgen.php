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
 * dbGetGroupSubscribers -> 
 * 	Reads in a group_id, dependant on $return type, returns :
 *   (list) an array of subscriber_id's in group_id
 *   (count) the # of subscibedrs in a group_id 
 *   (email) an array of emails in a group (for fast mail queue population)
 *
 *  if order_id is given, results will be ordered by the that particular field_id.	
 *    ordering can be changed from ASC to DESC if $order_type is provided (as 'DESC')
 *
 *  if limit is given, the array returned array will have a max # of entries. If
 *   start is given, the resultset will begin at the start entry
 */
 
 // TODO -> known bug: if you include/exclude a group w/ no criteria.. warnings are thrown.
 
require_once (bm_baseDir . '/inc/db_subscribers.php'); // 
 
function & dbGetGroupSubscribers(& $dbo, $table, $group_id, $returnType = 'list', $order_by = NULL, $order_type = 'ASC', $limit = NULL, $start = NULL, $searchText = NULL, $subscriber_id = null) {
	if ($table != 'subscribers' && $table != 'pending')
		die('<img src="' .
		bm_baseUrl . 'themes/shared/images/icons/alert.png" align="middle">Unknown table passed to dbGetGroupSubscribers().');

	// set variables to be appended onto SQL statements
	$sortTbl = '';
	$orderSQL = '';
	$limitStr = '';
	$whereSQL = ' WHERE 1';

        
        // Trevor Mills - had to add order_type into the Sort after the initial sort field.  
	if ($order_by) { // returned subscribers should be ordered 
        	require_once (bm_baseDir . '/inc/db_fields.php');
                $dbFields = dbGetFields($dbo);
		if ($group_id == 'all') {
			if ($order_by == 'email') {
				$sortTbl = ' INNER JOIN ' . $dbo->table[$table] . ' sort ON (t.' . $table . '_id=sort.' . $table . '_id)';
				$orderSQL = ' ORDER BY sort.email ' . $order_type . ' ,t.' . $table . '_id ' . $order_type;
			} else {
                                if ($dbFields[$order_by]['type'] == 'bigtext'){
                                        $useTable = $dbo->table[$table . '_bigdata'];
                                }
                                else{
                                        $useTable = $dbo->table[$table . '_data'];
                                }
				$sortTbl = ' LEFT JOIN ' . $useTable . ' sort ON (t.' . $table . '_id=sort.' . $table . '_id AND sort.field_id=' . $order_by . ')';
				$orderSQL = ' ORDER BY sort.value ' . $order_type . ' ,t.' . $table . '_id ' . $order_type;
			}
		} else {
			if ($order_by == 'email') {
				$sortTbl = ' INNER JOIN ' . $dbo->table[$table] . ' sort ON (t.' . $table . '_id=sort.' . $table . '_id)';
				$orderSQL = ' ORDER BY sort.email ' . $order_type . ' ,t.' . $table . '_id ' . $order_type;
			} else {
                                if ($dbFields[$order_by]['type'] == 'bigtext'){
                                        $useTable = $dbo->table[$table . '_bigdata'];
                                }
                                else{
                                        $useTable = $dbo->table[$table . '_data'];
                                }
				$sortTbl = ' LEFT JOIN ' . $useTable . ' sort ON (t.' . $table . '_id=sort.' . $table . '_id AND sort.field_id=' . $order_by . ')';
				$orderSQL = ' ORDER BY sort.value ' . $order_type . ' ,t.' . $table . '_id ' . $order_type;
			}
		}
	}
        
	if (is_numeric($group_id)) {
                // genSQL returns an array with 
                //      ['join_clause'] => the join clause for the lookup
                //      ['where_clause'] => the where clause for the lookup
                
		$sqlArray = genSql($dbo, $table, $group_id);
                
		if ($sqlArray['all_groups']) {
                        $group_id = 'all';
                        $orderSQL = ' ORDER BY sort.email,t.' . $table . '_id ' . $order_type;
			$sortTbl  = $sqlArray['join_clause']; 
		}
		else{
                        $criteriaTbl = $sqlArray['join_clause'];
			$whereSQL = $sqlArray['where_clause'];
		}
	}

        if ($searchText != NULL && $searchText != ''){
                $searchValues = quotesplit($searchText);
                //  Have to do a quick search for this text and then limit the final result to those IDs
                $searchSQL = 'SELECT DISTINCT(t.' . $table . '_id) FROM ' . $dbo->table[$table] . ' t ';
                $searchSQL.= 'LEFT JOIN ' . $dbo->table[$table] . '_data d on d.' . $table . '_id = t.' . $table . '_id ';
                $searchSQL.= 'LEFT JOIN ' . $dbo->table[$table] . '_bigdata bd on bd.' . $table . '_id = t.' . $table . '_id ';
                $searchSQL.= 'WHERE d.value REGEXP \'(' . implode('|',$searchValues) . ')\' or t.email REGEXP \'(' . implode('|',$searchValues) . ')\'';
                $searchSQL.= 'OR   bd.value REGEXP \'(' . implode('|',$searchValues) . ')\'';
		$result = & $dbo->getAll($searchSQL, 'row', '0');
                
                $whereSQL.= ' AND t.' . $table . '_id in (\'' . implode('\',\'',$result) . '\')';
        }

	if ($table == 'pending') // if viewing the pending table, only show subscribers to be added. Not those requesting changes...
		$whereSQL .= ' AND t.type=\'add\'';
		
	if (!empty($subscriber_id)){
		if (is_numeric($subscriber_id)){
			$subscriber_id = array($subscriber_id);
		}
		$whereSQL .= ' AND t.' . $table . '_id in ('.implode(',',$subscriber_id).')';
	}

        // If the return type starts with 'real_' we'll only return subscribers with a 
        // real (non-blank) email address
        if (strpos($returnType,'real_') === 0){
                if (defined('bm_BlankEmail')){
                        $whereSQL.= ' and (t.email <> \'' . bm_BlankEmail . '\'';
                        if (defined('bm_ParentEmailField')){
                            // Allow the email field to be blank if there is a parent email field
                            $searchSQL = 'SELECT DISTINCT(' . $table . '_id) FROM ' . $dbo->table[$table] . '_data ';
                            $searchSQL.= 'WHERE field_id = '.dbGetFieldId(bm_ParentEmailField).' AND value <> \'\'';
                    		$result = & $dbo->getAll($searchSQL, 'row', '0');
                
                            $whereSQL.= ' OR t.' . $table . '_id in (\'' . implode('\',\'',$result) . '\')';
                        }
                        $whereSQL.= ')';
                }
                //$returnType = substr($returnType,5);
        }

	if (function_exists('apply_filters')){
		$whereSQL = apply_filters('pommo_dbGetGroupSubscribers_whereSQL',$whereSQL,$dbo,$table);
	}
                
	switch ($returnType) {
		case 'count' :
		case 'real_count':
		    if ($returnType == 'count' or !defined('bm_ParentEmailField')){
    			if ($group_id == 'all')
    				$sql = 'SELECT COUNT(t.' . $table . '_id) FROM ' . $dbo->table[$table] . ' t' . $whereSQL;
    			else
    				$sql = 'SELECT COUNT(DISTINCT t.' . $table . '_id) FROM ' . $dbo->table[$table] .' t ' . $criteriaTbl . $whereSQL;
    			$result = & $dbo->query($sql, 0);
    		}
    		else{
    		    $tmp = dbGetGroupSubscribers($dbo, $table, $group_id, 'real_email', $order_by, $order_type, $limit, $start, $searchText,$subscriber_id);
    		    if (is_array($tmp)){
    		        $result = count($tmp);
    		    }
    		    else{
    		        $result = 0;
    		    }
    		}
			break;
		case 'list' : // only type which will apply a limit
			if (is_numeric($limit))
				if ($start)
					$limitStr = ' LIMIT ' . $start . ',' . $limit;
				else
					$limitStr = ' LIMIT ' . $limit;
			if ($group_id == 'all')
				$sql = 'SELECT DISTINCT t.' . $table . '_id FROM ' . $dbo->table[$table] . ' t' . $sortTbl . $whereSQL . $orderSQL . $limitStr;
			else
				$sql = 'SELECT DISTINCT t.' . $table . '_id FROM ' . $dbo->table[$table] . ' t' . $criteriaTbl . $sortTbl . $whereSQL . $orderSQL . $limitStr;
			
			$result = & $dbo->getAll($sql, 'row', '0');
			break;
		case 'email' : // grabs all emails
		case 'real_email' : // grabs all emails
			if ($group_id == 'all')
				$sql = 'SELECT t.email, t.' . $table . '_id FROM ' . $dbo->table[$table] . ' t' . $sortTbl . $whereSQL . $orderSQL;
			else
				$sql = 'SELECT DISTINCT t.email, t.'. $table .'_id FROM ' . $dbo->table[$table] . ' t' . $criteriaTbl . $sortTbl . $whereSQL . $orderSQL;
			if (!defined('bm_ParentEmailField') or !defined('bm_BlankEmail')){
			    $result = & $dbo->getAll($sql, 'row', '0');
			}
			else{
			    $tmpResult = & $dbo->getAll($sql, 'row');
			    $result = array();
			    foreach ($tmpResult as $r){
			        if ($r[0] == bm_BlankEmail){
			            // Have to look up the Parent Email Address
			            $sql = 'SELECT value FROM ' . $dbo->table[$table] . '_data WHERE ' . $table . '_id = ' . $r[1] . ' AND field_id = ' . dbGetFieldId(bm_ParentEmailField);
			            $row = & $dbo->getAll($sql, 'row', '0');
			            $tmpEmail = $row[0];
			        }
			        else{
			            $tmpEmail = $r[0];
			        }

			        if ($returnType == 'email' or ($tmpEmail != "" and $tmpEmail != bm_BlankEmail)){
			            $result[] = $tmpEmail;
					}
			    }
				$result = array_unique($result);
			}
			break;
		default :
			die('<img src="' . bm_baseUrl . 'themes/shared/images/icons/alert.png" align="middle">Unknown type sent to dbGetGroupSubscribers()');
	}

	return $result;
}

function & dbGetSubscriberGroups(& $dbo, $table, $subscriber_id, $returnType = 'list') {
	$groups = & dbGetGroups($dbo);
	$result = array();
	foreach (array_keys($groups) as $group_id){
		if (dbIsSubscriberInGroup($dbo,$subscriber_id,$group_id,$table)){
			$result[] = $group_id;
		}
	}
	return $result;
}

function dbIsSubscriberInGroup(& $dbo,$subscriber_id,$group_id,$table){
	$tmp = dbGetGroupSubscribers($dbo, $table, $group_id, 'count', NULL, 'ASC', NULL, NULL, NULL, $subscriber_id);
	return (empty($tmp) ? false : true);
}

function makedemo(& $tree, & $criteriaArray, $include = 'include', $oppLogic = array()) {
        $cl = $tree['criteria_logic'];
        $return = array(
                'field_ids' => array(),
                'filter_logic' => $cl,
        );
        
        // Setup a table of opposites
        if (empty($oppLogic)){
                $oppLogic['is_equal']           = 'not_equal';
                $oppLogic['not_equal']          = 'is_equal';
                $oppLogic['is_true']            = 'not_true';
                $oppLogic['not_true']           = 'is_true';
                $oppLogic['is_less']            = 'is_more';
                $oppLogic['is_more']            = 'is_less';
                $oppLogic['contains']           = 'does_not_contain';
                $oppLogic['does_not_contain']   = 'contains';
                $oppLogic['contains_multiple']  = 'does_not_contain_multiple';
                $oppLogic['does_not_contain_multiple']  = 'contains_multiple';
                $oppLogic['all']                = 'any';
                $oppLogic['any']                = 'all';
                $oppLogic['include']            = 'exclude';
                $oppLogic['exclude']            = 'include';
                $oppLogic['starts_with']        = 'does_not_start_with';
                $oppLogic['does_not_start_with']= 'starts_with';
        }

        if ($include == 'exclude'){
                $return['filter_logic'] = $oppLogic[$cl];
        }
                
        foreach (array_keys($tree) as $key) {
                if ($key != 'criteria_logic'){
                
		        $criteria = & $criteriaArray[$key];
                        
		        if (is_array($tree[$key])) {
                                // node is a group.  Need to call makedemo again to expand this group
                                if ($criteria['logic'] == 'not_in'){
                                        $tmp = makedemo($tree[$key],$criteriaArray,$oppLogic[$include], $oppLogic);
                                }
                                else{
                                        $tmp = makedemo($tree[$key],$criteriaArray,$include, $oppLogic);
                                }
                                $return['field_ids'] = array_unique(array_merge($return['field_ids'],$tmp['field_ids']));
                                
                                // Field_ids are only desired in the top level of the Return array
                                unset($tmp['field_ids']);
                                $return[] = $tmp;
                        }
                        else{
                                $return[] = array();
                                $return_key = array_pop(array_keys($return));
                                $return[$return_key] = array();
                                $return[$return_key]['field_id'] = $criteria['field_id'];
                                if ($include == 'include'){
                                        $return[$return_key]['logic'] = $criteria['logic'];
                                }
                                else{
                                        $return[$return_key]['logic'] = $oppLogic[$criteria['logic']];
                                }
                                $return[$return_key]['value'] = quotesplit($criteria['value']);
                                if (!in_array($criteria['field_id'],$return['field_ids'])){
                                        $return['field_ids'][] = $criteria['field_id'];
                                }
                        }
                }
        }
        return $return;
}

function db2db_array(& $array){
        $return = array();
        foreach ($array as $k=>$v){
                $return[$k] = db2db($v);
        }
        return $return;
}


function whereGen(& $fields, $connectorTable = array(), $logicTable = array()){
        
        if (empty($connectorTable)){
                $connectorTable['all'] = ' AND ';
                $connectorTable['any'] = ' OR ';
        }
        
        $connector = "";
        
        if (empty($logicTable)){
        	$logicTable['is_equal'] = '=';
        	$logicTable['not_equal'] = '<>';
        	$logicTable['is_more'] = '>';
        	$logicTable['is_less'] = '<';
        	$logicTable['contains'] = 'REGEXP';
        	$logicTable['does_not_contain'] = 'NOT REGEXP';
        	$logicTable['contains_multiple'] = 'REGEXP';
        	$logicTable['does_not_contain_multiple'] = 'NOT REGEXP';
        	$logicTable['starts_with'] = 'REGEXP';
        	$logicTable['does_not_contain'] = 'NOT REGEXP';
        }
        
        $return = ' ( ';
        
        foreach (array_keys($fields) as $key){
                if ($key !== 'filter_logic' && $key !== 'field_ids'){
                        $return .= $connector;
                        if (isset($fields[$key]['filter_logic'])){
                                $return.= whereGen($fields[$key], $connectorTable, $logicTable);
                        }
                        else{
                                //$value = db2db_array($fields[$key]['value']);
                                $value = $fields[$key]['value'];
                                $logic = $fields[$key]['logic'];
                		$count = count($value);
                		
                		$reg_exp_value = array();
                		foreach ($value as $k => $v){
                		    $reg_exp_value[$k] = preg_quote(preg_quote($v),"'");
                		}
                		reset($value);
                        $return.= ' ( t' . $fields[$key]['field_id'] . '.value ';
                		switch ($logic) {
                                case 'is_equal' :
                                        if ($count > 1){
                                                $return.= 'IN (\'' . implode ('\', \'',db2db_array($value)) .'\')'; 
                                        }
                                        else{
                                                $return.= $logicTable[$logic] . ' \'' . db2db(current($value)) . '\'';
                                        }
                                        break;
                                case 'not_equal' :
                                        if ($count > 1){
                                                $return.= 'NOT IN (\'' . implode ('\', \'',db2db_array($value)) .'\')'; 
                                        }
                                        else{
                                                $return.= $logicTable[$logic] . ' \'' . db2db(current($value)) . '\'';
                                        }
                                        $return.= ' OR t' . $fields[$key]['field_id'] . '.value IS NULL ';
                                        break;
                                case 'is_less' :
                                case 'is_more' : // cannot have multiple is more / is less.. 1st value is used if multiple..
                                        $return .= $logicTable[$logic] . '\'' . db2db(current($value)) . '\')';
                                        break;
                                case 'contains' :
                                        // The preg_quote(preg_quote(implode('|',$value)),"'") is to handle, within regular expressions
                                        // a few cases.  We need to be able to handle it when $value contains regular expression characters
                                        // so, that's why we use preg_quote.  It gets done twice cause, that's the way I could make this thing 
                                        // work.  The outer preg_quote spoofs the ' character as the delimiter, which essentially makes the 
                                        // string quote safe too.
                                        
                                        $return .= $logicTable[$logic] . ' \'(' . implode('|',$reg_exp_value) . ')\'';
                                        break;
                                case 'does_not_contain' :
                                        $return.= $logicTable[$logic] . ' \'(' . implode('|',$reg_exp_value) . ')\'';
                                        $return.= ' OR t' . $fields[$key]['field_id'] . '.value IS NULL ';
                                        break;
                                case 'contains_multiple' :
                                        $return .= $logicTable[$logic] . ' \'(^|,)(' . implode('|',$reg_exp_value) . ')(\$|,)\'';
                                        break;
                                case 'does_not_contain_multiple' :
                                        $return.= $logicTable[$logic] . ' \'(^|,)(' . implode('|',$reg_exp_value) . ')(\$|,)\'';
                                        $return.= ' OR t' . $fields[$key]['field_id'] . '.value IS NULL ';
                                        break;
                                case 'starts_with' :
                                        $return .= $logicTable[$logic] . ' \'^(' . implode('|',$reg_exp_value) . ')\'';
                                        break;
                                case 'does_not_starts_with' :
                                        $return.= $logicTable[$logic] . ' \'^(' . implode('|',$reg_exp_value) . ')\'';
                                        $return.= ' OR t' . $fields[$key]['field_id'] . '.value IS NULL ';
                                        break;
                                case 'is_true' :
                                        $return .= $logicTable['is_equal'] . '\'on\'';
                                        break;
                                case 'not_true' :
                                        $return .= $logicTable['not_equal'] . '\'on\'';
                                        $return.= ' OR t' . $fields[$key]['field_id'] . '.value IS NULL ';
                                        break;
                		}
                                $return.= ' )';
                                
                                
                        }
                        $connector = " ".$connectorTable[$fields['filter_logic']]." ";
                }
        }
        $return.= " ) ";
        
        return $return;

}

// crawls through a group's filtering criteria, returning an array tree of criteria_id's
//  the tree is a tree of criteria_id. 
function & dbCrawl(& $dbo, $group_id, & $criteriaArray, & $groupArray, & $groupsVisited) {

	// leave a breadcrumb...
	$groupsVisited[$group_id] = TRUE;
	$tree = array ();

	// Examine each criteria belonging to this group
	$group = & $groupArray[$group_id];
	foreach (array_keys($group) as $key) {
                if ($key !== 0){ // The first element contains the Filter Logic.
        		$criteria_id = & $group[$key];
        		$criteria = & $criteriaArray[$criteria_id];
        
        		// If criteria references another group..
        		if (($criteria['logic'] == 'is_in') || ($criteria['logic'] == 'not_in')) {
        			// check to make sure we haven't already been there [loop prevention!] 
        			if (!isset ($groupsVisited[$criteria['value']]))
        				$tree[$criteria_id] = dbCrawl($dbo, $criteria['value'], $criteriaArray, $groupArray, $groupsVisited);
        		} else // if not add the criteria_id to the tree and continue.
        			$tree[$criteria_id] = $criteria_id;
                }
	}
        $tree['criteria_logic'] = $group[0];
	return $tree;
}


function & genSql(& $dbo, & $table, & $group_id) {
        
        $return = array ();

	require_once (bm_baseDir . '/inc/lib.txt.php'); // used to convert value line (csv format)
	require_once (bm_baseDir . '/inc/db_groups.php');
	require_once (bm_baseDir . '/inc/db_fields.php');

	// TODO, remove this dependance on dbGroups....
	// get array of all criteria (saves from many MySQL queries). criteria_id is array key.
	//require_once (bm_baseDir.'/inc/db_groups.php');
	static $criteriaArray, $groupArray;
	if (!isset($criteriaArray)){
		$criteriaArray = dbGetGroupFilter($dbo);
		// make $groupArray where group_id is array key, and element is an array of that group's criteria_ids
		// ie. $groupArray[5 => array (6,12,15)]  means group 5  has filtering criteria w/ id 6,12, and 15 assosiated w/ it.
		$groupArray = array ();
		foreach (array_keys($criteriaArray) as $key) {
			$criteria = & $criteriaArray[$key];
			if (empty ($groupArray[$criteria['group_id']])){
				$groupArray[$criteria['group_id']] = array ();
			    array_push($groupArray[$criteria['group_id']], dbGroupGetFilterLogic($dbo,$criteria['group_id']));
	        }
			array_push($groupArray[$criteria['group_id']], $key);
		}
	}
        
	// determine if any criteria is assosiated with this group.. if not, return false.  
        // The place which called this will generate the proper Join & Where clauses
	if (empty ($groupArray[$group_id])){
                $return['all_groups'] = true;
                $return['join_clause'] = ' INNER JOIN ' . $dbo->table[$table] . ' sort ON (t.' . $table . '_id=sort.' . $table . '_id)';
                $return['where_clause'] = '';
                return $return;
        }

	// Recursively generate WHERE logic, returns SQL to match subscribers to be included and excluded
	$groupsVisited = array ();
	$tree = & dbCrawl($dbo, $group_id, $criteriaArray, $groupArray, $groupsVisited);

	// create array containing every field touched by this group's filtering process.
	$fields = makedemo($tree, $criteriaArray);
        
        // We need to return an array containing the JOIN Clause and the WHERE clause necessary
        // for the SQL call to get the subscribers belonging to this group
        
        // Here, we build the join clause and the where clause that we'll return.
        // The join is a Left join to allow for the possibility that a subscriber doesn't have
        // any data for a particular Field.  If the field is required to be there for the filter
        // then the logic in the WhereClause will take care of that. 
        $dbFields = dbGetFields($dbo);
        foreach ($fields['field_ids'] as $field_id){
                if ($fields[$field_id]['type'] == 'bigtext'){
                        $useTable = $dbo->table[$table . '_bigdata'];
                }
                else{
                        $useTable = $dbo->table[$table . '_data'];
                }
		$return['join_clause'] .= ' left join ' . $useTable . ' t' . $field_id . ' on t.' . $table .'_id = t' . $field_id . '.' . $table . '_id and t' . $field_id .'.field_id = \'' . $field_id . '\'';
        }
        
        // Now, call whereGen to get the where clause
        $return['where_clause'] = ' WHERE ' . whereGen($fields);
        
        return $return;

}

?>