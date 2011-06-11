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

// TODO -> code cleanups. Have a subscriber Object with ability to hold multiple
//  subscribers, their attributes (including pending status, etc.).. so as not to
//  do so many similar queries (esp. when involving confirmation codes/etc)

/** 
 * Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

// TODO -> better manage includes via common, etc.  NO MORE require_once [huge performance hit]!!!!
require_once (bm_baseDir.'/inc/lib.txt.php');
require_once (bm_baseDir.'/inc/db_fields.php');

// checks for the existance of an email in the pending, subscribers tables, or both if no table given.
function isDupeEmail(& $dbo, $email, $table = NULL,$exceptForID = "") {
	if ($table != 'subscribers') {
	$sql = 'SELECT email FROM '.$dbo->table['pending'].' WHERE email=\''.str2db($email).'\'';
	if ($dbo->query($sql,0))
		return true;
	if ($table == 'pending')
		return false;
	}
	$sql = 'SELECT email FROM '.$dbo->table['subscribers'].' WHERE email=\''.str2db($email).'\'';
        // Blank email addresses can be duplicated - they're the only ones
        if (defined('bm_BlankEmail')){
                $sql.= ' and email <> \''.bm_BlankEmail.'\'';
        }
        if ($exceptForID !== ""){
                $sql.= ' and subscribers_id <> \''.str2db($exceptForID).'\'';
        }
	if ($dbo->query($sql,0))
		return true;
	return false;
}

// Trevor Mills - adding in Password validation for login
if (defined('bm_PasswordField')){
function isPasswordCorrect(& $dbo,$email,$password){
    // Try to get the subscriber.  
    $subscriber = dbGetSubscriber($dbo,$email);
    if (!count($subscriber)){
        return null; // not in the table
    }
    else{
        $subscriber = current($subscriber);
        $PasswordID = dbGetFieldId(bm_PasswordField);
        if ($PasswordID){
            if (md5($password) == $subscriber['data'][$PasswordID]){
                return true;
            }
            else{
                return false;
            }
        }
    }
    return null;
}

function getPasswordCode(& $dbo,$email){
    // Try to get the subscriber.
    $subscriber = dbGetSubscriber($dbo,$email);
    if (!count($subscriber)){
        return null; // not in the table
    }
    else{
        $PasswordID = dbGetFieldId(bm_PasswordField);
        if ($PasswordID){
            $subscriber = current($subscriber);
            if ($subscriber['data'][$PasswordID]){
                return $subscriber['data'][$PasswordID];
            }
            else{
                return 'new';
            }
        }
    }
}

function resetSubscriberPassword(& $dbo,$email,$newpassword){
    // Try to get the subscriber.  
    $subscriber = dbGetSubscriber($dbo,$email);
    if (!count($subscriber)){
        return null; // not in the table
    }
    else{
        $subscriber = current($subscriber);
        $PasswordID = dbGetFieldId(bm_PasswordField);
        if ($PasswordID){
            $subscriber['data'][$PasswordID] = md5($newpassword);
            return dbSubscriberUpdate($dbo,$subscriber);
        }
    }
}
}

// For Parent Email Address
if (defined('bm_ParentEmailField')){
function dbChildrenExist(& $dbo,$arg){
	$query = 'SELECT count(*) FROM '.$dbo->table['subscribers_data'].' WHERE field_id=\'' . str2db(dbGetFieldId(bm_ParentEmailField)) . '\' AND value=';
	if (is_numeric($arg)){
		// an id
		$query.= '(SELECT `email` FROM '.$dbo->table['subscribers'].' WHERE `subscribers_id` = '.$arg.')';
		if (defined('bm_BlankEmail')){
			$query.= ' AND `email` <> \''.bm_BlankEmail.'\'';
		}
		$query.=')';
	}
	else{
		// an email
		$query.= '\''.str2db($arg).'\'';
	}
	return intval($dbo->query($query,0)) > 0;
}

function dbGetSubscriberFamily(& $dbo, $arg){
    // First, get the Child IDs
	$SubscriberIDs = dbGetChildrenIDs($dbo,$arg);
	
	// Then, get the parent ID
	$SubscriberIDs[] = array_pop(dbGetSubscriber($dbo,$arg,'id'));
	
	return dbGetSubscriber($dbo,$SubscriberIDs);
}   

function dbGetSubscriberChildren(& $dbo,$arg){
	$SubscriberIDs = dbGetChildrenIDs($dbo,$arg);
	
	if (empty($SubscriberIDs)){
		return array();
	}
	
	return dbGetSubscriber($dbo,$SubscriberIDs);
}

function dbGetChildrenIDs(& $dbo,$arg){
	$query = 'SELECT subscribers_id FROM '.$dbo->table['subscribers_data'].' WHERE field_id=\'' . str2db(dbGetFieldId(bm_ParentEmailField)) . '\' AND value=';
	if (is_numeric($arg)){
		// an id
		$query.= '(SELECT `email` FROM '.$dbo->table['subscribers'].' WHERE `subscribers_id` = '.$arg;
		if (defined('bm_BlankEmail')){
			$query.= ' AND `email` <> \''.bm_BlankEmail.'\'';
		}
		$query.=')';
	}
	else{
		// an email
		$query.= '\''.str2db($arg).'\'';
	}
	return $dbo->getAll($query,'assoc','subscribers_id');
}

function dbUpdateChildren(& $dbo,$OldEmail,$NewEmail){
    $query = 'UPDATE '.$dbo->table['subscribers_data'].' SET value=\'' . str2db($NewEmail) . '\' WHERE field_id=\'' . str2db(dbGetFieldId(bm_ParentEmailField)) . '\' AND value=\''.str2db($OldEmail).'\'';
    $dbo->query($query);
	if (function_exists('do_action')){
		do_action('pommo_update_children',$NewEmail,array(& $dbo));
	}
}

function dbDeleteChildren(& $dbo,$arg){
    if (!is_array($arg)){
        $arg = array($arg);
    }
    foreach ($arg as $e){
        if (is_numeric($e) or !defined('bm_BlankEmail') or $e != bm_BlankEmail){
            $Children = dbGetChildrenIDs($dbo,$e);
            if (is_array($Children) and count($Children)){
                dbSubscriberRemove($dbo,$Children);
            }
        }
    }
}
}
// dbGetSubscriber -> 
//  Reads in: Array of IDs, Array of Emails, ID, Email or Pending Code -- or 'all' to return all subscribers
//  Outputs depending on type passed:
//    (id) ID, (email) email, (code) Pending Code, or (detailed) Subscriber Details in *Subscriber Array* format
//  If an array is read in, an array of returnType will be returned representing the same order  the array was read in

// Subscirber Array format:
//   array[115] => array(      -- 115 == subscriber_id
// 		email => 'sub@scriber.com', 
//		date => '01/01/2006', 
//		data => array(
//				99 => 'Brice Burgess',   -- 99 == field_id
//				101 => 'Milwaukee'
//				)
//		)
function & dbGetSubscriber(& $dbo, $input, $retunType = 'detailed', $table = 'subscribers') {
	
	if (!is_array($input))
		$input = array ($input);
	if (isEmail($input[0]) || (defined('bm_BlankEmail') && $input[0] == bm_BlankEmail))
		$dbMatch = 's.email IN(\''.implode('\',\'', $input).'\')';
	elseif (is_numeric($input[0])) {
		$dbMatch = 's.'.$table.'_id IN ('.implode(',', $input).')';
		// set the ordering skeleton
		foreach ($input as $sid) {
			$subscribers[$sid] = array ('data' => array ());
		}
	}
	elseif ($input[0] == 'all')
		$dbMatch = '1';
	elseif ($table == 'pending') 
		$dbMatch = 's.code IN(\''.implode('\',\'', $input).'\')';
	
	$addFields = '';
	if ($table == 'pending')
		$addFields = ', s.newEmail';

	if (empty ($dbMatch)) {
	    ob_start();
	    var_dump($input);
	    $blah = ob_get_clean();
		die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbGetSubscriber() -> Bad Input Passed.'. $blah);
	}
	switch ($retunType) {
		case 'id' :
			$sql = 'SELECT s.'.$table.'_id FROM '.$dbo->table[$table].' s WHERE '.$dbMatch;
			return $dbo->getAll($sql, 'row', '0');
		case 'email' :
			$sql = 'SELECT s.email FROM '.$dbo->table[$table].' s WHERE '.$dbMatch;
			return $dbo->getAll($sql, 'row', '0');
		case 'code' :
			$sql = 'SELECT s.code FROM '.$dbo->table[$table].' s WHERE '.$dbMatch;
			return $dbo->getAll($sql, 'row', '0');
		case 'detailed' :
			$sql = 'SELECT DISTINCT s.'.$table.'_id,s.email,s.date,d.field_id,d.value,bd.value'.$addFields.' FROM '.$dbo->table[$table].' s LEFT JOIN '.$dbo->table[$table.'_data'].' d ON (s.'.$table.'_id = d.'.$table.'_id) LEFT JOIN '.$dbo->table[$table.'_bigdata'].' bd on (s.'.$table.'_id = bd.'.$table.'_id and bd.field_id = d.field_id) WHERE '.$dbMatch;
			$sArray = & $dbo->getAll($sql, 'row');
                        
                        if (!count($sArray)){
                                return array();
                        }

			foreach (array_keys($sArray) as $key) {
				$row = & $sArray[$key];
				// make the subscriber array if we haven't enountered this id before
				if (!isset ($subscribers[$row[0]]['email'])) {
					$subscribers[$row[0]] = array ('email' => & $row[1], 'date' => & $row[2], 'data' => array (), 'id' => & $row[0]);
					if (!empty($addFields))
						$subscribers[$row[0]]['newEmail'] =& $row[6]; // yes.. kludgey
				}
				// add subscriber_data value
                                // for 'bigtext', $row[4] will contain the first 60 characters of what's in $row[5]
				if (!empty($row[4]))
					$subscribers[$row[0]]['data'][$row[3]] = & $row[4];
				if (!empty($row[5])){
                                // BigData rows are the only fields that we'll allow newlines to appear in
					$subscribers[$row[0]]['bigdata'][$row[3]] = & $row[5];
					$subscribers[$row[0]]['bigdata'][$row[3]] = str_replace('\n', "\n",$subscribers[$row[0]]['bigdata'][$row[3]]);
					$subscribers[$row[0]]['bigdata'][$row[3]] = str_replace('\r', "\r",$subscribers[$row[0]]['bigdata'][$row[3]]);
                                }
			}
			return $subscribers;
	}
	die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbGetSubscriber() -> Reached end. Bad type sent?.');
}

function & dbGetSubscriberWithData(& $dbo, $data, $retunType = 'detailed', $table = 'subscribers'){
	// With data as an array of {field_id} => 'search_value', we'll look up subscribers matching the data
	if (!is_array($data)){
		die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbGetSubscriberWithData() -> Bad data sent.'.$data);
	}
	
	// Lookup the field IDs
	$field_ids = array();
	$dbMatchArray = array();
	$joinArray = array();
	$d = 0;
	foreach ($data as $field_name => $value){
		$field_id = dbGetFieldIdWithDBO($dbo,$field_name);
		if (!$field_id){
			die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbGetSubscriberWithData() -> Bad field name.'.$field_name);
		}
		$joinArray[] = 'LEFT JOIN '.$dbo->table[$table.'_data']." d$d ON (s.{$table}_id = d$d.{$table}_id)";
		$dbMatchArray[] = "d$d.field_id = $field_id AND d$d.value = '".mysql_real_escape_string($value)."'";
		$d++;
	}
	$join = join(" ",$joinArray);
	$dbMatch = '('.join(") AND (",$dbMatchArray).')';
	// Only will work on data (not bigdata)
	$sql = 'SELECT DISTINCT s.'.$table.'_id FROM '.$dbo->table[$table].' s '.$join.' WHERE '.$dbMatch;
	$sArray = & $dbo->getAll($sql, 'assoc');
	if (is_array($sArray) and count($sArray)){
		$ids = array();
		foreach ($sArray as $s){
			$ids[] = $s['subscribers_id'];
		}
		return dbGetSubscriber($dbo,$ids,$retunType,$table);
	}
	else{
		return array();
	}
}

// dbPending: Adds an entry to the pending table. Returns the confirmation code generated
//  Type is either "add", "del", or "mod" for Add Subscriber, Remove Subscriber, or Update Subscriber
//  $input is an array whose keys correlate to fields (columns) in a MySQL table
// fields must be passed as an array in order to be added to -- key = demo_id, value = demo_value

function dbPendingAdd(& $dbo, $type = NULL, $email = NULL, $fields = NULL) {
        $confirmation_key = md5(rand(0, 5000).time());
	$newEmail = '';
	
	switch ($type) {
		case 'change':
		case 'del' :
			if (!empty($fields['newEmail']) && isEmail($fields['newEmail']) && $fields['newEmail'] != $email)
				$newEmail = $fields['newEmail'];
				
			unset($fields['newEmail']); // must be done so as not to interfere with valuesStr below...
			unset($fields['newEmail2']);
            $subscribers = dbGetSubscriber($dbo,$email,'detailed');
            if (is_array($subscribers) and count($subscribers)){
                $subscriber = current($subscribers);
            }
            $pending_id = $subscriber['id'];

            // Have to set the values for hidden fields from the subscriber's current record
            if (is_array($fields)){
	            foreach ($subscriber['data'] as $key => $data){
	                if (!array_key_exists($key,$fields)){
	                    $fields[$key] = $data;
	                }
	            }
	            foreach ($subscriber['bigdata'] as $key => $data){
	                if (!array_key_exists($key,$fields)){
	                    $fields[$key] = $data;
	                }
	            }
	
                if (!isEmail($email)){
                    // a subscriber_id was passed in.  Get the person's email address
                    // ** Note the reassignement of $email **
                    $email = $subscriber['email'];
                }
            }
		case 'add' :
		case 'password' :
			// check to make sure no entries for this email already exist in pending table
			if (isDupeEmail($dbo, $email, 'pending')) 
				return false;

			// add email to pending table
                        if (is_numeric($pending_id)){
			        $sql = 'INSERT INTO '.$dbo->table['pending'].' SET pending_id=\''.str2db($pending_id).'\', code=\''.str2db($confirmation_key).'\', type=\''.str2db($type).'\', email=\''.str2db($email).'\', newEmail=\''.str2db($newEmail).'\', date=\''.date("Y-m-d").'\'';
			        $dbo->query($sql);
                        }
                        else{
			        $sql = 'INSERT INTO '.$dbo->table['pending'].' SET code=\''.str2db($confirmation_key).'\', type=\''.str2db($type).'\', email=\''.str2db($email).'\', newEmail=\''.str2db($newEmail).'\', date=\''.date("Y-m-d").'\'';
			        $dbo->query($sql);
			        // get ID of pending subscriber
			        $pending_id = $dbo->lastId();
                        }


			if (empty ($pending_id) || !is_numeric($pending_id))
				die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbAddPending() -> Unable to fetch pending_id. Notify Administrator.');

			// if fields were given, add them to the pending_data table
			if (!empty ($fields) && is_array($fields)){
                                $dbFields = dbGetFields($dbo);
				foreach (array_keys($fields) as $field_id) {
                                        // 'bigtext' is the only type that might have linebreaks.  They get stored on 'bigdata'
                                        // as /n/r, but are stripped for data
                                        if ($dbFields[$field_id]['type'] == 'bigtext'){
                                                $bigdata = str2db($fields[$field_id]);
                                                //$fields[$field_id] = preg_replace("/(\\\\n|\\\\r)/"," ",$fields[$field_id]);
                                                $fields[$field_id] = str_replace('\n'," ",$fields[$field_id]);
                                                $fields[$field_id] = str_replace('\r'," ",$fields[$field_id]);
                                        }
					// don't insert any null/blank values into pending_data
					if (!empty ($fields[$field_id])) {
						if (!isset ($values))
							$values = '('.str2db($pending_id).','.str2db($field_id).',\''.mysql_real_escape_string($fields[$field_id]).'\')';
						else
							$values .= ',('.str2db($pending_id).','.str2db($field_id).',\''.mysql_real_escape_string($fields[$field_id]).'\')';
					}
					if (!empty ($fields[$field_id]) && $dbFields[$field_id]['type'] == 'bigtext') {
						if (!isset ($bigvalues))
							$bigvalues = '('.str2db($pending_id).','.str2db($field_id).',\''.mysql_real_escape_string($bigdata).'\')';
						else
							$bigvalues .= ',('.str2db($pending_id).','.str2db($field_id).',\''.mysql_real_escape_string($bigdata).'\')';
                                        }
				}
			}

			if (!empty ($values)) {
				$sql = 'INSERT INTO '.$dbo->table['pending_data'].' (pending_id,field_id,value) VALUES '.$values;
				$dbo->query($sql);
			}
			if (!empty ($bigvalues)) {
				$sql = 'INSERT INTO '.$dbo->table['pending_bigdata'].' (pending_id,field_id,value) VALUES '.$bigvalues;
				$dbo->query($sql);
			}
			break;
		default:
			die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbAddPending() -> Unknown type passed.');
	}
	return $confirmation_key;
}

// dbPendingDel: <bool> Removes entries from pending & pending_data.
// you can pass a a code, email, or an array of either.
// returns the # of entries deleted
function dbPendingDel(& $dbo, $input = NULL) {
	if (empty ($input))
		return false;
		
	if (!is_array($input))
		$input = array($input);
		
        // Okay, I had to do major rewrite on this.  It's all based on the 
        // fact that I need to allow blank email addresses, and I 
        // need to persist the subscribers id from update to update
        // That means that I need to make the pending table pending_id
        // be the same as the subscriber_id on the subscriber table.
        // So, it's okay now to allow numeric values in the input 
        // here.  Gotta test the #&^%$ outta this.        

	// get IDs to purge
		$purge_ids = dbGetSubscriber($dbo, $input, 'id', 'pending');
		
	if (empty($purge_ids))
		return false; // nothing was deleted
		
	// TODO -> modify dbo->query() [or affected/records/etc] to take an array of SQL queries & return true if each one was successful. Then combine these
	$sql = 'DELETE FROM '.$dbo->table['pending_data'].' WHERE pending_id IN ('.implode(',', $purge_ids).')';
	$dbo->query($sql);

	$sql = 'DELETE FROM '.$dbo->table['pending_bigdata'].' WHERE pending_id IN ('.implode(',', $purge_ids).')';
	$dbo->query($sql);

	$sql = 'DELETE FROM '.$dbo->table['pending'].' WHERE pending_id IN ('.implode(',', $purge_ids).')';
	return $dbo->affected($sql); // return # of rows deleted
}

// dbSubscriberAdd: Adds a subscriber to the subsribers table. If the passed argument
//  is an array (in dbGetSubscriber format), the subscriber will be added using its data. 
//  If it's a code / email, the subscriber will be looked up in the pending table
function dbSubscriberAdd(& $dbo, & $arg, $pending = FALSE, $keep_id = FALSE) {
	if (is_array($arg)) {
		$subscriber = & $arg;
	}
	else { // adding subscriber FROM pending table
		$subscribers = & dbGetSubscriber($dbo, $arg, 'detailed', 'pending');
		$subscriber = & current($subscribers);
		// sanitize subscriber data  for re-insertion.
		$subscriber['data'] = dbSanitize($subscriber['data'], 'str');
		$subscriber['bigdata'] = dbSanitize($subscriber['bigdata'], 'str');
		$pending = TRUE;
	}
	
	// Sanity Checks
	if (!is_array($subscriber['data'])) $subscriber['data'] = array();
	if (!is_array($subscriber['bigdata'])) $subscriber['bigdata'] = array();
	
	// verify subscriber array is sane
	if (!is_array($subscriber) || empty ($subscriber['email']) || !isset ($subscriber['data']))
		die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbSubscriberAdd() -> Subscribers array not sane. Notify Administrator.');

	// set the date to today if it hasn't been provided	
	if (!isset ($subscriber['date']))
		$subscriber['date'] = date('Y-m-d');

        if (defined('bm_PasswordField') and $pending and (!defined('bm_BlankEmail') or $subscriber['email'] != bm_BlankEmail)){
            // Let's check and see if the subscriber is in there already.  
            // If they are, and the password field is blank, then 
            // we want to update the information based on what's in the pending table
            // This is to allow us to import subscribers and then have the
            // registration process for them behave seamlessly, like they were a 
            // new subscriber.   Aren't we tricky!
            $_sub = dbGetSubscriber($dbo,$subscriber['email']);
            if (is_array($_sub) and count($_sub)){
                $_sub = current($_sub);
                $PasswordFieldId = dbGetFieldId(bm_PasswordField);
                if (!$_sub['data'][$PasswordFieldId]){
                    // We don't want to clobber existing data, that's why we loop
                    foreach ($subscriber['data'] as $key => $data){
                        $_sub['data'][$key] = $data;
                    }
                    foreach ($subscriber['bigdata'] as $key => $data){
                        $_sub['bigdata'][$key] = $data;
                    }
                    dbSubscriberUpdate($dbo,$_sub);
            	    // remove subscriber from pending table
            		dbPendingDel($dbo, $arg);
            		return $_sub['id'];
                }
            }
        }
        // If $keep_id, we'll add in the ID that we've already got for this subscriber.
        // This is cause the "Update" process is actuall "Remove then Add".  However, if 
        // we're going to maintain any information about the user, then we have to persist
        // the id (I don't want to rely on the email address as the ID)
        if ($keep_id && is_numeric($subscriber['id'])){        
	        $sql = 'INSERT INTO '.$dbo->table['subscribers'].' (subscribers_id, email, date, lastModified) VALUES(\''.$subscriber['id'].'\',\''.$subscriber['email'].'\', \''.$subscriber['date'].'\', \''.date("Y-m-d H:i:s").'\')';
                $subscriber_id = $subscriber['id'];
	        $dbo->query($sql);
        }
        else{
             // insert new subscriber into subscribers, get ID
	        $sql = 'INSERT INTO '.$dbo->table['subscribers'].' (email, date,lastModified) VALUES(\''.$subscriber['email'].'\', \''.$subscriber['date'].'\', \''.date("Y-m-d H:i:s").'\')';
	        $dbo->query($sql);
	        $subscriber_id = $dbo->lastId();
        }

	if (empty ($subscriber_id) || !is_numeric($subscriber_id))
		die('<img src="'.bm_baseUrl.'themes/shared/images/icons/alert.png" align="middle">dbSubscriberAdd() -> Unable to fetch subscriber_id. Notify Administrator.');

	// insert subscriber's data into subscribers_data
        $dbFields = dbGetFields($dbo);
	foreach (array_keys($subscriber['data']) as $field_id) {
                // 'bigtext' is the only type that might have linebreaks.  They get stored on 'bigdata'
                // as /n/r, but are stripped for data
                if ($dbFields[$field_id]['type'] == 'bigtext'){
                        if ($subscriber['bigdata'][$field_id] != ""){
                                $bigdata = $subscriber['bigdata'][$field_id];
                        }
                        else{
                                $bigdata = $subscriber['data'][$field_id];
                        }
                        $subscriber['data'][$field_id] = str_replace('\n'," ",$subscriber['data'][$field_id]);
                        $subscriber['data'][$field_id] = str_replace('\r'," ",$subscriber['data'][$field_id]);
                }
		if (!isset ($values))
			$values = '('.$subscriber_id.','.$field_id.',\''.mysql_real_escape_string($subscriber['data'][$field_id]).'\')';
		else
			$values .= ',('.$subscriber_id.','.$field_id.',\''.mysql_real_escape_string($subscriber['data'][$field_id]).'\')';
                        
                if ($dbFields[$field_id]['type'] == 'bigtext'){
                        if (!isset ($bigvalues))
                                $bigvalues  = ' ('.$subscriber_id.','.$field_id.',\''. mysql_real_escape_string($bigdata) .'\')';
                        else
                                $bigvalues .= ',('.$subscriber_id.','.$field_id.',\''. mysql_real_escape_string($bigdata) .'\')';
                }
                                
	}
        
	if (!empty ($values)) {
		$sql = 'INSERT INTO '.$dbo->table['subscribers_data'].' (subscribers_id,field_id,value) VALUES '.$values;
		$dbo->query($sql);
	}

	if (!empty ($bigvalues)) {
		$sql = 'INSERT INTO '.$dbo->table['subscribers_bigdata'].' (subscribers_id,field_id,value) VALUES '.$bigvalues;
		$dbo->query($sql);
	}

	if ($pending) // remove subscriber from pending table if applicable.
		dbPendingDel($dbo, $arg);


	if (function_exists('do_action')){
		do_action('pommo_subscriber_add',$subscriber_id,array(& $dbo));
	}
	return $subscriber_id;
}

// dbUpdateFromPending: Updates a subscriber in the subsribers table.   If the passed argument
//  is an array, the subscriber will be updated with its data. If it's a code (not an array)
//  the subscriber will be updated with information from the pending table.
function dbSubscriberUpdate(& $dbo, & $arg, $pending = FALSE) {
		// TODO -> WAY TOO MANY QUERIES TAKE PLACE IN THIS PROCESS. FIX.
		//  perhaps pass email address from confirm page?? (vs. CODE, which is already looked up @ confirm page)

	$table = ($pending ? 'pending' : 'subscribers');
	if (is_array($arg)) {
		$subscriber = & $arg;
		$subscriber['data'] = dbSanitize($subscriber['data'], 'str');
	} else { // an email address was passed [note pending assignment]
		$subscribers = & dbGetSubscriber($dbo, $arg, 'detailed', 'pending');
		$subscriber = & current($subscribers);
		
		// Trevor Mills - I changed this from 'db' to 'str' because it was causing slashes to be written to the DB
		$subscriber['data'] = dbSanitize($subscriber['data'], 'str');
		
		$pending = TRUE;
	}

	// verify subscriber array is sane and the data array exists.
	if (!is_array($subscriber) || empty ($subscriber['email']) || !isset ($subscriber['data']))
		return false;
		
	// Okay, let's get down to it.  I need to make this much faster.  This delete then re-add 
	// method is crazy.  
	
	
	// Step 0: create the 'bigdata' array
	static $dbFields;
	if (defined('bm_PasswordField')){
		static $bm_PasswordFieldID;
	}
	if (!isset($dbFields)){
		$dbFields = dbGetFields($dbo);
		if (defined('bm_PasswordField')){
			$bm_PasswordFieldID = dbGetFieldIdWithDBO($dbo,bm_PasswordField);
		}
	}
	if (!is_array($subscriber['bigdata'])){
		$subscriber['bigdata'] = array();
	}
	foreach (array_keys($subscriber['data']) as $field_id) {
		// 'bigtext' is the only type that might have linebreaks.  They get stored on 'bigdata'
		// as /n/r, but are stripped for data
		if ($dbFields[$field_id]['type'] == 'bigtext'){
			if ($subscriber['bigdata'][$field_id] == ""){
				$subscriber['bigdata'][$field_id] = $subscriber['data'][$field_id];
			}
			$subscriber['data'][$field_id] = preg_replace('/[\n\r]/'," ",$subscriber['data'][$field_id]);
		}
	}
	
	// Step 1: Delete any data within data & bigdata that doesn't exist in the Subscriber to update
	$current_data_field_ids = array_keys($subscriber['data']);
	if (defined('bm_PasswordField') and !in_array($bm_PasswordFieldID,$current_data_field_ids)){
		$current_data_field_ids[] = $bm_PasswordFieldID;
	}
	$current_bigdata_field_ids = array_keys($subscriber['bigdata']);
	if (function_exists('apply_filters')){
		$current_data_field_ids = apply_filters('pommo_ignore_fields_on_update',$current_data_field_ids);
		$current_bigdata_field_ids = apply_filters('pommo_ignore_fields_on_update',$current_bigdata_field_ids);
	}
	$sql = 'DELETE FROM '.$dbo->table[$table.'_data'].' WHERE '.$table.'_id = '.$subscriber['id'].(count($current_data_field_ids) ? ' AND field_id NOT IN ('.join(',',$current_data_field_ids).')' : '');
	$dbo->query($sql);
	$sql = 'DELETE FROM '.$dbo->table[$table.'_bigdata'].' WHERE '.$table.'_id = '.$subscriber['id'].(count($current_bigdata_field_ids) ? ' AND field_id NOT IN ('.join(',',$current_bigdata_field_ids).')' : '');
	$dbo->query($sql);
	
	// Step 2: Insert all of the values.  We're using a MySQL Shortcut INSERT...ON DUPLICATE KEY
	if (count($subscriber['data'])){
		$values = array();
		$sql = 'INSERT INTO `'.$dbo->table[$table.'_data'].'` (`field_id`,`'.$table.'_id`,`value`) VALUES ';
		$sep = '';
		foreach ($subscriber['data'] as $key => $value){
			$sql.= $sep.'('.$key.','.$subscriber['id'].',\''.mysql_real_escape_string(preg_replace('/[\n\r]/'," ",$value)).'\')';
			$sep = ',';
		}
		$sql.= " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
		$dbo->query($sql);
	}

	// Step 2: Insert all of the values.  We're using a MySQL Shortcut INSERT...ON DUPLICATE KEY
	if (count($subscriber['bigdata'])){
		$values = array();
		$sql = 'INSERT INTO `'.$dbo->table[$table.'_bigdata'].'` (`field_id`,`'.$table.'_id`,`value`) VALUES ';
		$sep = '';
		foreach ($subscriber['bigdata'] as $key => $value){
			$sql.= $sep.'('.$key.','.$subscriber['id'].',\''.mysql_real_escape_string($value).'\')';
			$sep = ',';
		}
		$sql.= " ON DUPLICATE KEY UPDATE `value`=VALUES(`value`)";
		$dbo->query($sql);
	}
	
	if (!empty($subscriber['newEmail'])){ // set by pendingAdd (user update)
		if (defined('bm_ParentEmailField')){
		    dbUpdateChildren($dbo,$subscriber['email'],$subscriber['newEmail']);
		}
		$subscriber['email'] = $subscriber['newEmail'];
	}
	// Finally, update the email address, if it's changed
	$sql = 'UPDATE `'.$dbo->table[$table].'` SET `email` = \''.mysql_real_escape_string($subscriber['email']).'\' WHERE `'.$table.'_id` = '.$subscriber['id'];
	$dbo->query($sql);
	
	if (function_exists('do_action')){
		do_action('pommo_subscriber_update',$subscriber,($table == 'pending'),array(& $dbo));
	}
	if (defined('bm_ParentEmailField') and !empty($subscriber['oldEmail'])){
		dbUpdateChildren($dbo,$subscriber['oldEmail'],$subscriber['email']);
	}
	if ($pending and $table == 'subscribers'){
		// updated from a pending submission.  Safe to delete the Pending
		dbPendingDel($dbo, $arg);		
	}
	return true;
	
	/*
		
	if (false and defined('bm_ParentEmailField') and (!defined('bm_BlankEmail') or $subscriber['email'] != bm_BlankEmail) and dbChildrenExist($dbo,$subscriber['email'])){
		// Get the children so we can add them in again after they get deleted when calling dbSubscriberRemove below
		$Children = dbGetSubscriberChildren($dbo,$subscriber['email']);
	}
		
	
	// delete old subscriber information (from subscriber & pending table)
	dbSubscriberRemove($dbo, $subscriber['id'], $pending); 
	
	if (false and defined('bm_ParentEmailField')){
		if (!empty($Children) and !$pending){
			foreach ($Children as $Child){
				dbSubscriberAdd($dbo,$Child,false,true);
			}
		}
	}
	
	if (!empty($subscriber['newEmail'])){ // set by pendingAdd (user update)
		if (defined('bm_ParentEmailField')){
		    dbUpdateChildren($dbo,$subscriber['email'],$subscriber['newEmail']);
		}
		$subscriber['email'] = $subscriber['newEmail'];
	}

	// add new subscriber info
	return dbSubscriberAdd($dbo, $subscriber,false,true);
	*/
}

// dbSubscriberRemove: Pass an email address/array of or a pending 'code', and subscriber
//  will be purged from the subscribers, subscribers_data, and pending tables(s)
function dbSubscriberRemove(& $dbo, & $arg, $pending = FALSE) {

	if (is_array($arg) || isEmail($arg) || is_numeric($arg))
		$subscribers = & dbGetSubscriber($dbo, $arg, 'id');
	else {
		$subscribers = & dbGetSubscriber($dbo, $arg, 'id', 'pending');
		if (defined('bm_ParentEmailField')){
		    $tmp = & dbGetSubscriber($dbo, $arg, 'email', 'pending');
		    if (is_array($tmp)){
		        dbDeleteChildren($dbo,$tmp[0]);
		    }
		}
	}
	
	// if not removing from the pending table, we'll remove children as well
	if (!$pending and defined('bm_ParentEmailField')){
		dbDeleteChildren($dbo,$subscribers);
	}
	
	// verify subscriber array is sane, and first subscriber_id is numeric
	if (empty ($subscribers) || !is_numeric($subscribers[0]))
		return false;
		
	// delete from subscribers table
	$sql = 'DELETE FROM '.$dbo->table['subscribers'].' WHERE subscribers_id IN ('.implode(',', $subscribers).')';
	
	$dbo->query($sql);

	// delete from subscribers_data tables
	$sql = 'DELETE FROM '.$dbo->table['subscribers_data'].' WHERE subscribers_id IN ('.implode(',', $subscribers).')';
	$dbo->query($sql);

	$sql = 'DELETE FROM '.$dbo->table['subscribers_bigdata'].' WHERE subscribers_id IN ('.implode(',', $subscribers).')';
	$dbo->query($sql);

	dbPendingDel($dbo, $arg); // purge entries in pending

	if (function_exists('do_action')){
		do_action('pommo_subscriber_remove',$subscribers,$pending,array(& $dbo));
	}
	
	return true;
}

// takes in an array of subscriber ids, and flags them with given type.
// TODO --> add support for flagging an email / array of emails..
function dbFlagSubscribers($subscribers, $type = 'update') {
	if(!is_array($subscribers) || !is_numeric($subscribers[0]))
		bmKill('Non subscriber ID passed to flagSubscribers()');
		
	global $dbo;
	
	foreach ($subscribers as $subscriber_id) {
		if (!isset ($values))
			$values = '('.$subscriber_id.',\''.$type.'\')';
		else
			$values .= ',('.$subscriber_id.',\''.$type.'\')';
	}
	if (!empty ($values)) {
		$sql = 'INSERT INTO '.$dbo->table['subscribers_flagged'].' (subscribers_id,flagged_type) VALUES '.$values;
		if ($dbo->query($sql))
			return true;
	}
	return false;
}
?>