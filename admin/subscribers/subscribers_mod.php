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

// TODO -> page needs to be re-written. It has only been re-worked to fit new demo/subs system.
 /**********************************
	INITIALIZATION METHODS
 *********************************/
define('_IS_VALID', TRUE);

require('../../bootstrap.php');
require_once (bm_baseDir.'/inc/db_subscribers.php');
require_once (bm_baseDir.'/inc/db_fields.php');
require_once (bm_baseDir.'/inc/lib.txt.php');
require_once (bm_baseDir.'/inc/lib.validate_subscriber.php');

$poMMo = & fireup('secure');
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();
$smarty->assign('returnStr', _T('Subscribers Manage'));

// sanity check
if ($_REQUEST['table'] != 'subscribers' && $_REQUEST['table'] != 'pending' || empty ($_REQUEST['action']) || empty ($_REQUEST['sid']) && $_REQUEST['action'] != 'create')
	bmRedirect('subscribers_manage');

$table = $_REQUEST['table'];

$appendUrl = "limit=".$_REQUEST['limit']."&order=".$_REQUEST['order']."&orderType=".$_REQUEST['orderType']."&group_id=".$_REQUEST['group_id']."&table=".$_REQUEST['table'];

// CHECK REQUESTS
if (!empty ($_POST['deleteEmails']) || !empty ($_POST['deleteIDs'])) {
	// deleteion confirmation recieved...
        if ($table == 'pending'){
                if (!empty($_POST['deleteEmails'])){
                    dbPendingDel($dbo, $_POST['deleteEmails']);
                }
                if (!empty($_POST['deleteIDs'])){
                    dbPendingDel($dbo, $_POST['deleteIDs']);
                }
        }
        else{
                if (!empty($_POST['deleteEmails'])){
                        dbSubscriberRemove($dbo, $_POST['deleteEmails']);
                        if (defined('bm_ParentEmailField')){
                            dbDeleteChildren($dbo,$_POST['deleteEmails']);
                        }
                }
                if (!empty($_POST['deleteIDs'])){
                        dbSubscriberRemove($dbo, $_POST['deleteIDs']);
                }
        }

	bmRedirect('subscribers_manage.php?'.$appendUrl);
}
elseif (!empty ($_POST['addEmails'])) {
	// add to subscribers recieved (pending -> subscribers)
	foreach ($_REQUEST['addEmails'] as $email) {
		dbSubscriberAdd($dbo,$email);
	}
	bmRedirect('subscribers_manage.php?'.$appendUrl);
}
elseif (!empty ($_REQUEST['editId'])) {
	// edit update was recieved...
	$updates = array();

	// create dbGetSubscriber compatible array
	foreach ($_REQUEST['editId'] as $key) {
        
	        // Trevor Mills - In order to take advantage of the Validate Form class
	        // we need to kloodge the values.  The Validate Form Class expects
	        // the variables to be named in a certain way.  We'll abide by that...
	        if (defined('bm_BlankEmail') && $_REQUEST['email'][$key] == ""){
	                $_REQUEST['email'][$key] = bm_BlankEmail;
	        }
            if (!empty($_REQUEST["email"][$key])){
                $_POST['bm_email'] = $_REQUEST['email'][$key];
                $_POST['d'] = $_REQUEST['d'][$key];
                $isAdminCalling = true;
                if (validateSubscribeForm(true,$key,$isAdminCalling)){
		        $a = array ('id' => $key, 'email' => $_REQUEST['email'][$key], 'date' => $_REQUEST['date'][$key], 'data' => array ());
		        if ($a['email'] != $_REQUEST['oldEmail'][$key])
			        $a['oldEmail'] = $_REQUEST['oldEmail'][$key];
        		foreach (array_keys($_REQUEST['d'][$key]) as $field_id) {
        			$subVal = & $_REQUEST['d'][$key][$field_id];
        			if (!empty ($subVal))
        				$a['data'][$field_id] = $subVal;
        		}
        		$updates[] = $a;		
                }
            }
	}
        

	foreach ($updates as $subscriber) {
                if ($_POST['submit'] == 'Add'){
		        dbSubscriberAdd($dbo,$subscriber);
                }
                else{
		        dbSubscriberUpdate($dbo,$subscriber);
                }
	}
	
	if (defined('bm_ParentEmailField')){
	    foreach ($updates as $subscriber){
	        if ($subscriber['oldEmail'] != ""){
	            dbUpdateChildren($dbo,$subscriber['oldEmail'],$subscriber['email']);
	        }
	    }
	}

        if (!$logger->isErr()){
	        bmRedirect('subscribers_manage.php?'.$appendUrl);
        }
}



// BEGIN MAIN PAGE
$fields = dbGetFields($dbo);


switch ($_REQUEST['action']) {
	case "edit" :

	if (is_array($_REQUEST['sid']) && count($_REQUEST['sid']) > 15) {
		$_REQUEST['sid'] = array_slice($_REQUEST['sid'], 0, 15);
		$subCount = 15;
		$smarty->assign('cropped', TRUE);
	}
	$subscribers = dbGetSubscriber($dbo, $_REQUEST['sid'], 'detailed', $table);
        foreach (array_keys($subscribers) as $key){
                $subscribers[$key]['old_email'] = $subscribers[$key]['email'];
        }
        
        // Reassign the subscribers data from $_REQUEST so that the forms will 
        // redisplay the data the user entered (only on Submit)
        if ($_REQUEST['submit']){       
                foreach (array_keys($subscribers) as $key){
                        $subscribers[$key]['email'] = $_REQUEST['email'][$key];
                        foreach ($_REQUEST['d'][$key] as $field_id => $value){
                                if ($value !== ""){
                                        $subscribers[$key]['data'][$field_id] = $value;
                                }
                                else{
                                        unset($subscribers[$key]['data'][$field_id]);
                                }
                        }
                }
        }        

	$smarty->assign('subscribers',$subscribers);
		break;

	case "delete" :
	
	$subscribers = dbGetSubscriber($dbo, $_REQUEST['sid'], 'detailed', $table);
        $emails = array();
        $ids = array();
        foreach ($subscribers as $subscriber){
                if (defined('bm_BlankEmail') and $subscriber['email'] == bm_BlankEmail){
                        $ids[] = array('display' => $subscriber['email'], 'id' => $subscriber['id']);
                }
                else{
                        $emails[] = array('display' => $subscriber['email'], 'email' => $subscriber['email']);
                }
        }
        $smarty->assign('emails',$emails);
        $smarty->assign('ids',$ids);
		break;

	case "add" :
	
	$emails = dbGetSubscriber($dbo, $_REQUEST['sid'], 'email', $table);
	$smarty->assign('emails',$emails);
		break;
                
        case "create" :
        $subscribers = array();
        if (empty ($_REQUEST['max_rows'])){
                $max_rows = 1;
        }
        else{
                $max_rows = $_REQUEST['max_rows'];
        }
        
        for ($i = 0 ; $i < $max_rows; $i++){
                $subscriber = array();
                $subscriber["email"] = $_REQUEST["email"][$i];
                $subscriber["date"] = date("Y-m-d");
                $data = array();
                if (is_array($_REQUEST['d'][$i])){
                        foreach ($_REQUEST['d'][$i] as $k => $v){
                                $data[$k] = $v;
                        }
                }
                $subscriber["data"] = $data;
                $subscribers[] = $subscriber;
        }
	$smarty->assign('subscribers',$subscribers);
                break;
                
                

		
}

if (!is_array($_REQUEST['sid'])){
        $_REQUEST['sid'] = array($_REQUEST['sid']);
}


$smarty->assign('fields',$fields);
$smarty->assign('sid',$_REQUEST['sid']);
$smarty->assign('action',$_REQUEST['action']);
$smarty->assign('max_rows',$max_rows);


$smarty->assign('table',$table);
$smarty->assign('group_id',$group_id);
$smarty->assign('limit',$limit);
$smarty->assign('order',$order);
$smarty->assign('orderType',$orderType);
if (defined('bm_ParentEmailField')){
    $smarty->assign('parentEmailFieldID',dbGetFieldId(bm_ParentEmailField));
}
if (defined('bm_PasswordField')){
    $smarty->assign('passwordFieldID',dbGetFieldId(bm_PasswordField));
}

$smarty->display('admin/subscribers/subscribers_mod.tpl');
bmKill();
?>