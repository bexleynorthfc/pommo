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
 * Don't allow direct access to this file. Must be called from
elsewhere
*/
defined('_IS_VALID') or die('Move along...');

// returns true if valid.. false if not. Adds errors/messages to logger.
// The allow ID
function validateSubscribeForm($dupeCheck = TRUE,$exceptForID = "",$isAdminCalling = false) {
	global $logger;
	global $dbo;
	require_once (bm_baseDir . '/inc/lib.txt.php');
        
        $ignoreCheckBecauseEmailBlank = (defined('bm_BlankEmail') && $_POST['bm_email'] == bm_BlankEmail);

	// ** check for correct email syntax
	if (!isEmail($_POST['bm_email']) && !$ignoreCheckBecauseEmailBlank){
		$logger->addErr(_T('Invalid Email Address'));
		if (defined('bm_BlankEmail') and $_POST['bm_email'] == ""){
		    $InputArray = $_POST;
		    $InputArray['bm_email'] = bm_BlankEmail;
		    $link = bm_baseUrl.'user/subscribe.php?input='.urlencode(serialize($InputArray));
			$link = bmAppendPoMMoInstance($link);
		    $logger->addErr('<strong>If you do not have an email address</strong>, but still wish to register as a '.bm_SubscriberWord.', enter <strong><a href="'.$link.'">'.bm_BlankEmail.'</a></strong> in the email field');
		}
    }
		
	// ** check if confirmation email matches. (if exists)
	if (isset($_POST['updateForm']) && $_POST['email2'] != $_POST['bm_email'])
		$logger->addErr(_T('Emails must match.'));

	// ** check if email already exists in DB ("duplicates are bad..")
	if ($dupeCheck && !$ignoreCheckBecauseEmailBlank) {
		if (isDupeEmail($dbo, $_POST['bm_email'],'pending',$exceptForID)) {
			$logger->addErr('You already have a pending registration in our system.');
			$logger->addErr('You should have received a confirmation email from us.');
			$logger->addErr('Check your junk folder, it might be in there.');
			$logger->addErr('If you\'d like, we can <a href=\''.bm_baseUrl.'user/process.php?reconfirm=true&email='.$_POST['bm_email'].bmAppendPoMMoInstance("&").'\'>resend the confirmation email</a>.');
		}
		elseif (isDupeEmail($dbo, $_POST['bm_email'],'subscribers',$exceptForID)) {
		    $PassDupeCheck = false;
		    if (defined('bm_PasswordField')){
		        // We're going to check if the person is in there, but has no password 
		        // That would indicate that they were added in manually and we should update
		        // whatever info is in the database with what they entered on the screen
		        $_PasswordFieldID = dbGetFieldId(bm_PasswordField);
				if (isset($_POST['d'][$_PasswordFieldID]) or (isset($_POST['bm_password']))){
			        $_sub = dbGetSubscriber($dbo,$_POST['bm_email']);
			        if (is_array($_sub)){
			            $_sub = current($_sub);
			            if (!$_sub['data'][$_PasswordFieldID]){
			                // No password assigned
			                $PassDupeCheck = true;
			            }
			        }
				}		        
		    }
		    if (!$PassDupeCheck){
    			$logger->addErr('Email address already exists. Duplicates are not allowed');
				if ($isAdminCalling){
	    			if (defined('bm_ParentEmailField')){
	    			    $logger->addErr('To register someone else under an existing email address, enter the email address in the Parent Email field and leave the Email field blank.');
	    			}
				}
				else{
	    			$logger->addErr('Since you\'re already on the system, you can <a href=\''.bm_baseUrl.'user/login.php'.bmAppendPoMMoInstance("?").'\'>login</a> to change your settings.  There\'s a link on the login page to set/reset your password');
	    			$logger->addErr('This registration did not go through.');
				}
			    
    			global $smarty;
    	
    			if (is_object($smarty))
    				$smarty->assign('dupe', TRUE);
    		}
		}
	}
	
	if ($isAdminCalling and defined('bm_ParentEmailField')){
	    $ParentEmailField = dbGetFieldId(bm_ParentEmailField);
	    if ($_POST['d'][$ParentEmailField] == bm_BlankEmail){
	        $_POST['d'][$ParentEmailField] = "";
	    }
	    if ($_POST['d'][$ParentEmailField] != "" and $_POST['bm_email'] != "" and $_POST['bm_email'] != bm_BlankEmail){
    		$logger->addErr(_T('You cannot enter a value in BOTH the email and the parent email fields.  An email address can go in one or the other, and the other must be left blank'));
    		$logger->addErr(_T('To register someone else under an existing email address, enter the email address in the Parent Email field and leave the Email field blank.'));
	    }
	    elseif($_POST['d'][$ParentEmailField] != "" and !isDupeEmail($dbo,$_POST['d'][$ParentEmailField])){
    		$logger->addErr(_T('The email address you entered in the Parent Email Field does not exist on this system.  You must use an existing email address.'));
    	}
	        
	}
        
        // For the type 'multiplemultiple' the choices are in an array called $_POST['choices']
        // This little bit of code is to massage the choices into a format that the processor 
        // can handle (it only likes strings, not arrays)
        if (!empty($_POST['choices']) && is_array($_POST['choices'])){
                foreach ($_POST['choices'] as $k => $v){
                        $_POST['d'][$k] = implode(',',$v);
                }
        }
        
	// ** validate user submitted fields
	$fields = & dbGetFields($dbo, 'active');
	$subscriber_data = array ();
	
	// ** Trevor Mills - Added password validation to poMMo.  
	// Validate Password 
	if (defined('bm_PasswordField')){
    	if ($_POST['bm_password'] != "" and $_POST['bm_password'] != $_POST['bm_password2']){
    		$logger->addErr(_T('The passwords you entered do not match.'));
    	}
	}
	
	if (!empty($fields)) {
	foreach (array_keys($fields) as $field_id) {
		$field = & $fields[$field_id];

		// check to make sure a required field is not empty
        
		if (empty ($_POST['d'][$field_id]) && $field['required'] == 'on' && !$isAdminCalling ||
                ($isAdminCalling && defined('bm_adminAllowBlankRequired') && !bm_adminAllowBlankRequired)) {
			$logger->addErr($field['prompt'] . ' ' . _T('was a required field.'));
			continue;
		}

		// create field array
		if (!empty ($_POST['d'][$field_id])) {
			// TODO : insert validation schemes here (ie. check options, #, date)
			switch ($field['type']) {
				case 'checkbox' :
					if ($_POST['d'][$field_id] == 'on') // don't add to subscriber_data if value is not checked..
						$subscriber_data[$field_id] = str2db($_POST['d'][$field_id]);
					break;
				default :
					$subscriber_data[$field_id] = str2db($_POST['d'][$field_id]);
					break;
			}

		}
	}
	}
	if ($logger->isErr())
		return false;
	return true;
}
?>


