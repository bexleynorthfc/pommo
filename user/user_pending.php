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

require ('../bootstrap.php');
require_once (bm_baseDir . '/inc/lib.txt.php');
require_once (bm_baseDir . '/inc/lib.mailings.php');

$poMMo = & fireup('keep');
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();

if ($poMMo->get('pommo_input')) {
	$input = (unserialize($poMMo->get('pommo_input')));
}

if (!isEmail($input['email']))
	bmRedirect('login.php');

$sql = "SELECT type,code,email FROM {$dbo->table['pending']} WHERE email='" . str2db($input['email']) . "'";
$dbo->query($sql);
$row = & mysql_fetch_assoc($dbo->_result);

// check if user wants to reconfirm or cancel their request
if (!empty ($_POST)) {
	if (isset ($_POST['reconfirm'])) {
		switch ($row['type']) {
			case "add" :
				bmSendConfirmation($row['email'], $row['code'], "subscribe");
				break;
			case "change" :
				bmSendConfirmation($row['email'], $row['code'], "update");
				break;
			case "del" :
				bmSendConfirmation($row['email'], $row['code'], "unsubscribe");
				break;
			case "password" :
				bmSendConfirmation($row['email'], $row['code'], "password");
		}
		$logger->addMsg(sprintf(_T('A confirmation email has been sent to %s. It should arrive within the next few minutes. Please follow its instructions to complete your request. Thanks!'),$input['email']));
	} else {
		require_once (bm_baseDir . '/inc/db_subscribers.php');
		if (dbPendingDel($dbo, $row['code']))
			$logger->addMsg(_T('Your pending request has been cancelled.'));
		else
			$logger->addErr(_T('Error cancelling your request. Contact the administrator.'));
	}
	
	$smarty->assign('nodisplay',TRUE);

} else {
	switch ($row['type']) {
		case "add" :
		case "del" :
		case "change" :
		case "password" :
			$logger->addMsg(_T('You have pending changes. Please respond to your confirmation email'));
			$logger->addMsg(_T('It\'s possible the confirmation email was identified as spam.  Please check your junk folder.'));
    		$listConfig = $poMMo->getConfig(array (
    			'list_fromemail',
    		));
    		$logger->addMsg(_T('To ensure emails sent to you from this system are not identified as spam, please add '. $listConfig['list_fromemail'] . ' to your address book or spam whitelist.'));
			break;
		default :
			$url = '';
			$logger->addErr(sprintf(_T('Please Try Again! %s login %s'), '<a href="' . bm_baseUrl . 'user/login.php">', '</a>'));
                        $smarty->assign('content',$smarty->myFetch('user/user_pending.tpl'));
                        $smarty->display('user/user_page.tpl');
			bmKill();
	}
}
$smarty->assign('content',$smarty->myFetch('user/user_pending.tpl'));
$smarty->display('user/user_page.tpl');
bmKill();
?>