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
require_once (bm_baseDir . '/inc/class.json.php');
require_once (bm_baseDir . '/inc/db_templates.php'); // Mailing History Database Handling

$poMMo = & fireup('secure','keep');
$dbo = & $poMMo->_dbo;

// make JSON return
$json = array();
$encoder = new json;

$mailingData = $poMMo->get('mailingData');
$json['status'] = 'success';
$prefix = "Draft: ";
if (!$mailingData){
	$json['status'] = 'failed';
}
else{
	if ($_GET['action'] == 'lookup'){
		$json['name'] = $mailingData['subject'];
		if ($id = dbGetTemplateID($dbo,$prefix.$mailingData['subject'])){
			$json['exists'] = true;
		}
		else{
			$json['exists'] = false;
		}
	}
	elseif($_POST['action'] == 'save'){
		$template = array();
		$template['name'] = $prefix.$mailingData['subject'];
		$template['body'] = stripslashes(str_replace('__ampersand__','&',$_POST['body']));
		if($id = dbGetTemplateID($dbo,$prefix.$mailingData['subject'])){
			$template['id'] = $id;
			$result = dbUpdateTemplate($dbo, $template);
		}		
		else{
			$result = dbAddTemplate($dbo, $template);
		}
		if (!$result){
			$json['status'] = 'failed';
		}
	}
}


header('x-json: '.$encoder->encode($json));
exit();
?>