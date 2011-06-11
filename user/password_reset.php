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
$smarty->assign('title', $poMMo->_config['site_name'] . ' - ' . _T('password reset'));

$smarty->prepareForForm();

if (!defined('bm_PasswordField')){
    bmRedirect('login.php');
}

if (empty($_POST)) {
	// ___ USER HAS NOT SENT FORM ___
	
	// Assign email to form if pre-provided
	if (isset($_REQUEST['Email']))
		$smarty->assign('Email',$_REQUEST['Email']);
	elseif (isset($_REQUEST['email']))
		$smarty->assign('Email',$_REQUEST['email']);
		
	if (isset($_REQUEST['code'])){
		$smarty->assign('code',$_REQUEST['code']);
	}
} else {
	// ___ USER HAS SENT FORM ___

	if (isDupeEmail($dbo, $_POST['Email'], 'pending')) {
		// __EMAIL IN PENDING TABLE, REDIRECT
		$input = urlencode(serialize(array('email' => $_POST['Email'])));
		SmartyValidate :: disconnect();
		bmRedirect('user_pending.php?input='.$input);
	}
	elseif (!isDupeEmail($dbo, $_POST['Email'], 'subscribers')){
		// __ REPORT STATUS
		$logger->addMsg(_T('That email address was not found in our system. Please try again or ')."<a href='".bm_baseUrl."user/subscribe.php'>register here</a>.");
	}
	else{
	    // What state are we in?
	    if ($_POST['code'] != ""){
	        if ($_POST['bm_password'] == ""){
		        $logger->addMsg(_T('You must enter a new password.'));
	        }
	        elseif ($_POST['bm_password'] != $_POST['bm_password2']){
		        $logger->addMsg(_T('The passwords you entered do not match, please try again.'));
		    }
		    elseif ($_POST['code'] != getPasswordCode($dbo,$_POST['Email'])){
		        $logger->addMsg(_T('Sorry, we could not verify the confirmation code.  Please ')."<a href='password_reset.php'>"._T('try again')."</a>");
		    }
		    else{
		        resetSubscriberPassword($dbo,$_POST['Email'],$_POST['bm_password']);
		        $logger->addMsg(_T('Password successfully reset.  Proceed to ')."<a href='".bm_baseUrl."user/login.php'>"._T('login page')."</a>");
	            $smarty->assign('hideform',true);
		    }
		}
		else{
	        require_once (bm_baseDir . '/inc/lib.mailings.php');
		    bmSendConfirmation($_POST['Email'], getPasswordCode($dbo,$_POST['Email']), "password");
		    $logger->addMsg(_T('An email has been sent to you with instructions for resetting your password.  Please check your inbox in a moment and follow the instructions in that email.'));
	        $smarty->assign('hideform',true);
		}
	} 
	$smarty->assign($_POST);
}

if ($_REQUEST['returning']){
    $smarty->assign('returning',true);
}

$listConfig = $poMMo->getConfig(array (
	'list_fromemail',
));
$smarty->assign('admin_email',$listConfig['list_fromemail']);


$smarty->assign('content',$smarty->myFetch('user/reset_password.tpl'));
$smarty->display('user/user_page.tpl');
bmKill();
?>