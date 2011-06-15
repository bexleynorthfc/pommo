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
require (bm_baseDir . '/inc/lib.validate_subscriber.php');
require_once (bm_baseDir . '/inc/db_subscribers.php');
require_once (bm_baseDir . '/inc/db_fields.php');
require_once (bm_baseDir . '/inc/lib.mailings.php');
require_once (bm_baseDir . '/inc/lib.txt.php');

$poMMo = & fireup('keep');
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();

// Prepare for subscriber form -- load in fields + POST/Saved Subscribe Form
$smarty->prepareForSubscribeForm(); 

$_POST['bm_email'] = $smarty->get_template_vars('bm_email');
if (defined('bm_PasswordField')){
    $_POST['passcode'] = $smarty->get_template_vars('passcode');
    if ($_POST['original_email'] != ""){
        $Email = $_POST['original_email'];
    }
    else{
        $Email = $_POST['bm_email'];
    }
    if ($_POST['passcode'] != getPasswordCode($dbo,$Email)){
		bmRedirect('login.php');
	}
	$smarty->assign('passwordFieldID',dbGetFieldId(bm_PasswordField));
}
if (defined('bm_ParentEmailField') and $smarty->get_template_vars('subscriber_id') != ""){
    $_POST['subscriber_id'] = $smarty->get_template_vars('subscriber_id');
}

if (empty($_POST['bm_email']))
		bmRedirect('login.php');
		
if(!empty ($_POST['delete'])) {
    // User requested to delete the current family member
    dbSubscriberRemove($dbo,$_POST['subscriber_id']);
    unset($_POST['subscriber_id']);
	// We've deleted the subscriber, we need to do a bmRedirect removing the subscriber_id from the $poMMo->get('pommo_input')
	$input = unserialize($poMMo->get('pommo_input'));
	unset($input['subscriber_id']);
	$poMMo->set('pommo_input',$input);
	bmRedirect('user_update.php');
}
 
if (defined('bm_ParentEmailField') and dbChildrenExist($dbo,$_POST['bm_email'])){
    
    $Family = dbGetSubscriberFamily($dbo,$_POST['bm_email']);
    $FirstNameFieldID = dbGetFieldId(__("First Name"));
    $LastNameFieldID = dbGetFieldId(__("Last Name"));

    function sortFamily($a,$b){
        global $FirstNameFieldID, $LastNameFieldID;
        if ($a['data'][$FirstNameFieldID] < $b['data'][$FirstNameFieldID]){
            return -1;
        }
        elseif ($a['data'][$FirstNameFieldID] > $b['data'][$FirstNameFieldID]){
            return 1;
        }
        else{
            if ($a['data'][$LastNameFieldID] < $b['data'][$LastNameFieldID]){
                return -1;
            }
            elseif ($a['data'][$LastNameFieldID] > $b['data'][$LastNameFieldID]){
                return 1;
            }
        }
        return 0;
    }

    uasort($Family,'sortFamily');

    foreach ($Family as $key => $Subscriber){
        $Family[$key]['data']['FullName'] = $Subscriber['data'][$FirstNameFieldID]." ".$Subscriber['data'][$LastNameFieldID];
    }
    
    $smarty->assign('Family',$Family);
    $smarty->assign('ParentID',array_pop(dbGetSubscriber($dbo,$_POST['bm_email'],'id')));
}

// populates form values with subscribers info from DB (called when POST vals not present)
function bmPopulate() {
	global $dbo;
	global $smarty;
	
	if (defined('bm_ParentEmailField') and $_POST['subscriber_id'] != ""){
	    $subscribers = & dbGetSubscriber($dbo, str2db($_POST['subscriber_id']), 'detailed');
	    $smarty->assign('subscriber_id',$_POST['subscriber_id']);
	}
	else{
	    $subscribers = & dbGetSubscriber($dbo, str2db($_POST['bm_email']), 'detailed');
	}
	if (empty($subscribers))
		bmRedirect('login.php');
		
	$subscriber_id = & key($subscribers); // subscriber's ID
	$subscriber = & current($subscribers);
	if (defined('bm_ParentEmailField') and dbChildrenExist($dbo,$_POST['bm_email'])){
	    $smarty->assign('current_id',$subscriber['id']);
	}

		
	$smarty->assign('original_email', $_POST['bm_email']);
	$smarty->assign('email2', $_POST['bm_email']);
	$smarty->assign('d', $subscriber['data']); 
	$smarty->assign('bd', $subscriber['bigdata']); 
	
	

        $fields = & dbGetFields($dbo);
        foreach (array_keys($fields) as $field_id){
                if ($fields[$field_id]['type'] == 'multiplemultiple'){
                        if ($subscriber['data'][$field_id]){
                                if (empty($choices)){
                                        $choices = array();
                                }
                                $choices_to_check = explode(',',$subscriber['data'][$field_id]);
                                foreach ($choices_to_check as $choice){
                                        $index = array_search($choice,$fields[$field_id]['options']) + 1;
                                        $choices[$field_id][$index] = "on";
                                }
                        }
                }
        }
        if (!empty($choices)){
                $smarty->assign('choices',$choices);
        }
}

if (!empty ($_POST['update'])) {
	// validate new subscriber info
	if ($_POST['original_email'] != $_POST['bm_email'] and isDupeEmail($dbo,$_POST['bm_email'])){
		$logger->addErr('Email address already exists. Duplicates are not allowed');
	}
	elseif (validateSubscribeForm(FALSE)) {  
		// allow user to change their email address
		if ($_POST['original_email'] != $_POST['bm_email'])
			$_POST['d']['newEmail'] = $_POST['bm_email'];
			
		if (defined('bm_ParentEmailField') and $_POST['subscriber_id'] != ""){
		    $key = $_POST['subscriber_id'];
		}
		else{
		    $key = $_POST['original_email'];
		}
		if (!defined('bm_ParentEmailField') or $key != 'new'){
		    $code = dbPendingAdd($dbo, 'change', $key, $_POST['d']);
		}
		else{
		    $_POST['d'][dbGetFieldId(bm_ParentEmailField)] = $_POST['bm_email'];
            $code = dbPendingAdd($dbo, 'add', bm_BlankEmail, $_POST['d']);
		}
		if (empty ($code)) {
			$logger->addMsg(_T('The system could not process your request. Perhaps you already have requested a change?  Check your inbox for a confirmation email.') . 
			sprintf(_T('%s Click Here %s to try again.'),'<a href="'.bm_baseUrl.'user/login.php">','</a>'));
		} else {
		    if (defined('bm_PasswordField')){
		        $InputArray = array('bm_email' => $_POST['bm_email'],'passcode' => $_POST['passcode']);
		        if (defined('bm_ParentEmailField') and $_POST['subscriber_id'] != ""){
		            $InputArray['subscriber_id'] = $_POST['subscriber_id'];
	            }
    		    $input = urlencode(serialize($InputArray));
		        bmRedirect('confirm.php?code='.$code.'&input='.$input);
		        //$logger->addMsg(_T("<a href='".bm_baseUrl."user/confirm.php?code=$code&input=".$input."'>go there</a>"));
		    }
		    else{
    			bmSendConfirmation($_POST['original_email'], $code, "update");
    			$logger->addMsg(_T('Update request received.') . ' ' . _T('A confirmation email has been sent. You should receive this letter within the next few minutes. Please follow its instructions.'));
    		}
		}
	}
}
elseif (!empty ($_POST['unsubscribe'])) {
	$code = dbPendingAdd($dbo, "del", $_POST['original_email']);
	if (empty ($code))
		$logger->addMsg(_T('The system could not process your request. Perhaps you already have requested a change?') .
		sprintf(_T('%s Click Here %s to try again.'),'<a href="'.bm_baseUrl.'user/login.php">','</a>'));
	else {
		bmSendConfirmation($_POST['original_email'], $code, "unsubscribe");
		$logger->addMsg(_T('Unsubscribe request received.') . ' ' . _T('A confirmation email has been sent. You should receive this letter within the next few minutes. Please follow its instructions.'));
	}
	$messages = $logger->getMsg();
	$content = '<div class="msgdisplay">'."\n";
	foreach ($messages as $msg){
		$content.= "<div>* $msg</div>\n";
	}
	$content.= '</div>'."\n";
	$smarty->assign('content',$content);
	$smarty->display('user/user_page.tpl');
	bmKill();
}
elseif (!empty ($_POST['add_new'])){
	$smarty->assign('original_email', $_POST['bm_email']);
	$smarty->assign('email2', $_POST['bm_email']);
    
}
else { // both update + unsubsscribe empty...
	bmPopulate();
}

if (defined('bm_PasswordField')){
    $smarty->assign('usingPassword',true);
}
if (defined('bm_ParentEmailField')){
    $smarty->assign('usingParentEmail',true);
}

global $poMMo_package;
if (is_a($poMMo_package,'Package') and method_exists($poMMo_package,'assignSmarty')){
	$poMMo_package->assignSmarty($smarty,'user_update');
}

$config = $poMMo->getConfig(array (
	'admin_email'
));
$smarty->assign('AdminEmail',$config['admin_email']);


$smarty->assign('selfURL',$_SERVER['SCRIPT_NAME'].'?'.$_SERVER['QUERY_STRING']);
$smarty->assign('content',$smarty->myFetch('user/user_update.tpl'));
$smarty->display('user/user_page.tpl');
bmKill();
?>