<?php /** [BEGIN HEADER] **
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
 
 // TODO: Combine these mailing confirmation functions... they repeat.
 
 /** 
 * Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

// send a confirmation email
function bmSendConfirmation($to, $confirmation_key, $type) {
	if (empty($confirmation_key) || empty ($to) || empty($type)) 
		return false;
	
	global $poMMo;
	$logger = & $poMMo->_logger;
		
	$dbvalues = $poMMo->getConfig(array('messages'));
	$messages = unserialize($dbvalues['messages']);
	
	$subject = $messages[$type]['sub'];
	
	if (defined('bm_PasswordField') and $type == 'password'){
	    $url = bm_http.bm_baseUrl.'user/password_reset.php?email='.urlencode($to).'&code='.$confirmation_key;
	}
	elseif ($type == 'notify'){
	    $url = bm_http.bm_baseUrl.'admin/subscribers/subscribers_manage.php';
	    global $dbo;
	    $sql = 'SELECT group_id FROM '.$dbo->table['groups'].' WHERE group_name = \'Registered\'';
	    $group_id = $dbo->query($sql, 0);
	    if (is_numeric($group_id)){
	        $url.= '?group_id='.$group_id;
	    }
	}
	else{
	    $url = bm_http.bm_baseUrl.'user/confirm.php?code='.$confirmation_key;
	}
	$url = bmAppendPoMMoInstance($url);
	$body = preg_replace('@\[\[URL\]\]@i',$url,$messages[$type]['msg']);  
	if ($type == 'notify'){
	    if (is_numeric($confirmation_key)){
	        // a subscriber id was passed in.  Get the info 
    		// First we'll create the info array of the information they entered on the form
    		$subscriber = array_pop(dbGetSubscriber($dbo,$confirmation_key));
    		$fields = dbGetFields($dbo);
    		$InfoArray = array('email' => $subscriber['email']);
    		foreach ($fields as $field_id => $field){
    		    if ($field['active'] == "on"){
    		        if ($field['type'] == 'bigtext'){
    		            $data = 'bigdata';
    		        }
    		        else{
    		            $data = 'data';
    		        }
    		        $InfoArray[$field['name']] = $subscriber[$data][$field_id];
    		    }
    		}
	    }
	    elseif(is_array($confirmation_key)){
	        $InfoArray = $confirmation_key;
	    }
	    else{
	        $InfoArray = array();
	    }
	    $Info = "";
	    foreach ($InfoArray as $label => $value){
	        if ($value !== null){
	            $Info.= "$label: $value\n";
	        }
	    }
	    
	    // If they're not available, then don't send the email
	    if (isset($InfoArray['Availability']) and $InfoArray['Availability'] != "Yes"){
	        return false;
	    }
	    
	    $body = preg_replace('@\[\[INFO\]\]@i',$Info,$body);  
	}
	
	if (empty($subject) || empty($body))
		return false;
	
	require_once(bm_baseDir.'/inc/class.bmailer.php');
	$message = new bMailer;
	
	// allow mail to be sent, even if demo mode is on
	$message->toggleDemoMode("off");
	
	// send the confirmation mail
	$message->prepareMail($subject, $body);
	if ($message->bmSendmail($to)) {
		$message->toggleDemoMode();
	    if ($type != 'notify'){
		    $logger->addMsg(_T("An email has been sent to you.  Please ensure that ".$message->_fromemail." is in your address book or spam whitelist."));
		}
		return true; // mailing was a sucess...
	}
	// reset demo mode to default
	$message->toggleDemoMode();
	
	$logger->addErr(_T('Error Sending Mail'));
	return false;	
}

// Sends a "test" mailing to an address, returns <string> status.
function bmSendTestMailing(&$to, &$input) {
	require_once (bm_baseDir.'/inc/class.bmailer.php');
	require_once (bm_baseDir.'/inc/lib.txt.php');
		$Mail = new bMailer($input['fromname'], $input['fromemail'], $input['frombounce'],NULL,NULL,$input['charset']);
		$altbody = NULL;
		$html = FALSE;
		if ($input['ishtml'] == 'html')
			$html = TRUE;
		if (!empty($input['altbody']) && $input['altInclude'] == 'yes')
			$altbody = str2str($input['altbody']);
		if (!$Mail->prepareMail(str2str($input['subject']), str2str($input['body']), $html, $altbody)) 
			return '(Errors Preparing Test)';
		
		if (!$Mail->bmSendmail($to))
			return _T('Error Sending Mail');
		return sprintf(_T('Test sent to %s'), $to);
}

	
?>