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

//(ct)

/**********************************
	INITIALIZATION METHODS
 *********************************/
define('_IS_VALID', TRUE);
 
require('../../bootstrap.php');
require_once (bm_baseDir.'/inc/db_templates.php');
require_once (bm_baseDir.'/inc/db_fields.php');

$poMMo =& fireup("secure");
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/

// default key/value pairs of this page's state
$pmState = array(
	'templateid' => NULL,
	'action' => NULL
);
$poMMo->stateInit('mailings_templates_mod',$pmState);

$action = $poMMo->stateVar('action',$_REQUEST['action']);
$templateid = $poMMo->stateVar('templateid',$_REQUEST['templateid']);

// if templateid or action are empty - redirect
// TODO -> perhaps perform better validation of action/mailID here
//  e.g. have a validType($var,'rule') function? i.e. validType($templateid,numeirc)
if (empty($action) || (empty($templateid) and $action != 'create')) {
	bmRedirect('mailings_templates.php');
}

// perform deletions if requested
if (!empty($_REQUEST['deleteTemplates']) && !empty($_REQUEST['delid'])) {
	if (dbRemoveTemplates($dbo, $_REQUEST['delid']))
		bmRedirect('mailings_templates.php');
	else
		$logger->addErr(_T('Trouble deleteing templates'));
}

if (!empty($_REQUEST['saveTemplate'])) {
	$_REQUEST = array_map(stripslashes,$_REQUEST);
	$template = array();
	$template['name'] = $_REQUEST['name'];
	$template['body'] = $_REQUEST['body'];
	if (!empty($_REQUEST['templateid'])){
		$template['id'] = $_REQUEST['templateid'];
		$result = dbUpdateTemplate($dbo, $template);
	}
	else{
		$result = dbAddTemplate($dbo, $template);
		if ($result){
			$action = 'edit';
			$template['id'] = $dbo->lastID();
		}
	}
	$templateid = $template['id'];
	if ($result){
		$logger->addMsg(_T('Template successfully saved'));
	}
	else{
		$logger->addErr(_T('Problem saving template'));
	}
}

$smarty = & bmSmartyInit();
$smarty->assign('returnStr', _T('Mailing Templates'));
$smarty->assign('templateid',$templateid);
$smarty->assign('action',$action);

// fetch subscriber fields for use with personaliztion selector
// Get array of fields. Key is ID, value is an array of the demo's info
$fields = dbGetFields($dbo);
if (!empty($fields))
	$smarty->assign('fields', $fields);

// ACTIONS -> choose what we want to do.
switch ($action) {

	case 'edit': 
		$smarty->assign('actionStr', _T('Template Edit'));
		$noassign = TRUE;					
	case 'delete': 
		$templates = dbGetTemplateInfo($dbo, $templateid);
		if (!isset($noassign))
			$smarty->assign('actionStr', _T('Template Delete'));
		$smarty->assign('templates',$templates);		
		break;
	case 'create': 
		$smarty->assign('actionStr', _T('Template Create'));
		$smarty->assign('templates',array(array()));
		break;
	default:
		bmKill('Error; unknown action.');
} //switch
	
$smarty->display('admin/mailings/mailings_templates_mod.tpl');
bmKill();
?>