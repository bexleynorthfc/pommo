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
 
/**********************************
	INITIALIZATION METHODS
 *********************************/
define('_IS_VALID', TRUE);

require ('../../bootstrap.php');
require_once (bm_baseDir . '/inc/db_mailing.php');
require_once (bm_baseDir.'/inc/db_groups.php');
require_once (bm_baseDir . '/inc/lib.txt.php');
require_once (bm_baseDir.'/inc/db_sqlgen.php');

$poMMo = & fireup('secure', 'keep');
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;


/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();

if ($_REQUEST['cancel'] == 'TRUE'){
	// unset the mailingData
	$poMMo->set(array('mailingData' => array()));
	bmRedirect('admin_mailings.php');
}

// check to see if a mailing is taking place (queue not empty)
if (!mailingQueueEmpty($dbo)) {
	bmKill(sprintf(_T('A mailing is already taking place. Please allow it to finish before creating another. Return to the %s Mailing Page %s'), '<a href="admin_mailings.php">', '</a>'));
}

if ($poMMo->get('mode') != ''){
	$_REQUEST['mode'] = $poMMo->get('mode');
}
$input = $poMMo->get('mailingData');

if ($input['mailgroup'] == 'found_set'){
    $groupName = _T("Found set of subscribers")." (".(strlen($input['searchText']) > 80 ? substr_replace(htmlspecialchars($input['searchText']),'...',80) : $input['searchText']).")";
    if ($input['group_id'] != ""){
        $group = $input['group_id'];
    }
    else{
        $group = 'all';
    }
	if (is_numeric($input['searchText']) or strpos($input['searchText'],',') !== false){
	    $subscriberCount = dbGetGroupSubscribers($dbo, 'subscribers', $group, 'real_count', NULL, 'ASC', NULL, NULL, '', split(',',$input['searchText']));
	}
	else{
	    $subscriberCount = dbGetGroupSubscribers($dbo, 'subscribers', $group, 'real_count', NULL, 'ASC', NULL, NULL, $input['searchText']);
	}
}
else{
    $groupName = dbGroupName($dbo, $input['mailgroup']);
    $subscriberCount = dbGetGroupSubscribers($dbo, 'subscribers', $input['mailgroup'], 'real_count');
}
$input['subscriberCount'] = $subscriberCount;
$input['groupName'] = $groupName;
if (function_exists('apply_filters')){
	$input = apply_filters('pommo_massage_mailing_data',$input);
}

// redirect (restart) if body or group id are null...
if (empty($input['mailgroup']) || empty($input['body'])) {
	bmRedirect('mailings_send.php');
}

// send a test mail to an address if requested
if (!empty($_POST['testMail'])) {
	if (isEmail($_POST['testTo'])) {
		require_once (bm_baseDir.'/inc/lib.mailings.php');
		$logger->addMsg(bmSendTestMailing($_POST['testTo'],$input));	
		}
	else
		$logger->addMsg(_T('Invalid Email Address'));
}

// if sendaway variable is set (user confirmed mailing parameters), send mailing & redirect.
if (!empty ($_GET['sendaway'])) {
	if (intval($subscriberCount) >= 1) {
		$securityCode = dbMailingCreate($dbo, $input);
		if ($input['mailgroup'] == 'found_set'){
            if ($input['group_id'] != ""){
                $group = $input['group_id'];
            }
            else{
                $group = 'all';
            }
			if (is_numeric($input['searchText']) or strpos($input['searchText'],',') !== false){
				$subscribers = dbGetGroupSubscribers($dbo, 'subscribers', $group, 'real_email', NULL, 'ASC', NULL, NULL,'', split(',',$input['searchText']));
			}
			else{
				$subscribers = dbGetGroupSubscribers($dbo, 'subscribers', $group, 'real_email', NULL, 'ASC', NULL, NULL, $input['searchText']);
			}
		}
		else{
		    $subscribers = dbGetGroupSubscribers($dbo, 'subscribers', $input['mailgroup'], 'real_email');
		}
		if (function_exists('apply_filters')){
			$subscribers = apply_filters('pommo_subscribers_to_queue',$subscribers,$input);
		}
	    dbQueueCreate($dbo, $subscribers);
		dbMailingStamp($dbo, "start");
		
		if (bmHttpSpawn(bm_baseUrl.'admin/mailings/mailings_send4.php?securityCode='.$securityCode)) {
			sleep(1); // allows mailing to begin...
			bmRedirect('mailing_status.php');
		}
		//die (bm_baseUrl.'admin/mailings/mailings_send4.php?securityCode='.$securityCode);
	}
	else {
		$logger->addMsg(_T('Cannot send a mailing to 0 subscribers!'));
	}
}

$smarty->assign($input);
$smarty->display('admin/mailings/mailings_send3.tpl');

?>
