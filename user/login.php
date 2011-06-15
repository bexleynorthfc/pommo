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
require_once (bm_baseDir . '/inc/db_subscribers.php');

$poMMo = & fireup();
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();
$smarty->assign('title', $poMMo->_config['site_name'] . ' - ' . _T('subscriber logon'));

$smarty->prepareForForm();

if (!SmartyValidate :: is_registered_form() || empty($_POST)) {
	// ___ USER HAS NOT SENT FORM ___
	SmartyValidate :: connect($smarty, true);
	SmartyValidate :: register_validator('email', 'Email', 'isEmail', false, false, 'trim');

	$formError = array ();
	$formError['email'] = _T('Invalid email address');
	$smarty->assign('formError', $formError);
	
	// Assign email to form if pre-provided
	if (isset($_REQUEST['Email']))
		$smarty->assign('Email',$_REQUEST['Email']);
	elseif (isset($_REQUEST['email']))
		$smarty->assign('Email',$_REQUEST['email']);
		
} else {
	// ___ USER HAS SENT FORM ___
	SmartyValidate :: connect($smarty);
	if (SmartyValidate :: is_valid($_POST)) {
		// __ FORM IS VALID __
		
		if (isDupeEmail($dbo, $_POST['Email'], 'pending')) {
			// __EMAIL IN PENDING TABLE, REDIRECT
			$input = urlencode(serialize(array('email' => $_POST['Email'])));
			SmartyValidate :: disconnect();
			bmRedirect('user_pending.php?input='.$input);
		}
		elseif (defined('bm_PasswordField')){
		    $result = isPasswordCorrect($dbo,$_POST['Email'],$_POST['bm_password']);
		    if ($result === true){
    			SmartyValidate :: disconnect();
    			// __ EMAIL IN SUBSCRIBERS TABLE, AND PASSWORD IS CORRECT - REDIRECT
				$poMMo->set(array('pommo_input' => serialize(array('bm_email' => $_POST['Email'],'passcode' => getPasswordCode($dbo,$_POST['Email'])))));
        		bmRedirect('user_update.php');
        	}
        	elseif ($result === false){
    			// __ EMAIL IN SUBSCRIBERS TABLE, BUT PASSWORD IS INCORRECT
		        $logger->addMsg(_T('You are on our system, but you have entered the incorrect password.  Please try again or ')."<a href='".bm_baseUrl."user/password_reset.php'>reset your password</a>");
		    }
			else{
    			// __ EMAIL NOT IN SUBSCRIBERS TABLE
		        $logger->addMsg(_T('That email address was not found in our system. Please try again or ')."<a href='".bm_baseUrl."user/subscribe.php'>register here</a>");
			}
		}
		elseif (isDupeEmail($dbo, $_POST['Email'], 'subscribers')) {
			// __ EMAIL IN SUBSCRIBERS TABLE, REDIRECT
			$input = urlencode(serialize(array('bm_email' => $_POST['Email'])));
			SmartyValidate :: disconnect();
		    bmRedirect('user_update.php?input='.$input);
		} else {
			// __ REPORT STATUS
			$logger->addMsg(_T('That email address was not found in our system. Please try again.'));
		}
	}
	$smarty->assign($_POST);
}

if (defined('bm_PasswordField')){
    $smarty->assign('usingPassword',true);
}

$smarty->assign('content',$smarty->myFetch('user/login.tpl'));
$smarty->display('user/user_page.tpl');
bmKill();
?>