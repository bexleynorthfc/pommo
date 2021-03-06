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
 
 /* TODO --> make cool w/  b) no fields   ,   c) blank fields [edit field creation to auto fill prompt, etc.] */
 // TODO --> enhance w/ AJAX prefetching .... rework[cleanup] this page
 
 /**********************************
	INITIALIZATION METHODS
 *********************************/
define('_IS_VALID', TRUE);

require('../../bootstrap.php');
require_once (bm_baseDir.'/inc/db_subscribers.php');
require_once (bm_baseDir.'/inc/db_groups.php');
require_once (bm_baseDir.'/inc/db_sqlgen.php');
require_once (bm_baseDir.'/inc/db_fields.php');
require_once (bm_baseDir.'/inc/class.pager.php');

$poMMo = & fireup('secure');
$logger = & $poMMo->_logger;
$dbo = & $poMMo->_dbo;

/** Setup Variables
 * 
 * fields = array of all fields (key is field_id)
 * groups = array of all groups (key is group_id)
 * table = table to perform lookup on. Either 'subscribers' or 'pending''
 * group_id = The ID of the group being viewed. If none set to "all" for all subscribers
 * limit = The Maximum # of subscribers to show per page
 * order = The field (field_id) to order subscribers by
 * orderType = type of ordering (ascending - ASC /descending - DESC)
 * appendUrl = all the values strung together in HTTP_GET form
 */

$fields = dbGetFields($dbo);
$groups = dbGetGroups($dbo);
$table = (empty ($_REQUEST['table'])) ? 'subscribers' : str2db($_REQUEST['table']);
if (empty ($_REQUEST['group_id'])){
	if (is_a($poMMo_package,'Package') and method_exists($poMMo_package,'getDefaultGroup')){
		$group_id = $poMMo_package->getDefaultGroup($dbo);
	}
	else{
		$group_id = 'all';
	}
}
else{
    $group_id = str2db($_REQUEST['group_id']);
}
$limit = (empty ($_REQUEST['limit'])) ? '500' : str2db($_REQUEST['limit']);
if (empty ($_REQUEST['order'])){
	global $poMMo_package;
	if (is_a($poMMo_package,'Package') and method_exists($poMMo_package,'getDefaultOrder')){
		$order = $poMMo_package->getDefaultOrder($dbo);
	}
	else{
		$order = 'email';
	}
}
else{
    $order = str2db($_REQUEST['order']);
}
$orderType = (empty ($_REQUEST['orderType'])) ? 'ASC' : str2db($_REQUEST['orderType']);
$searchText = (empty ($_REQUEST['searchText'])) ? '' : str2db($_REQUEST['searchText']);
$appendUrl = '&table='.$table.'&limit='.$limit."&order=".$order."&orderType=".$orderType."&group_id=".$group_id.'&searchText='.$searchText;

if (!empty ($_REQUEST['bFormSubmitted'])){
    unset($_GET['page']);
}

// Get a count -- TODO implement group object so this could be made into a 'list',
//   and then a partial list of subscribers_ids fed to the 'detailed' query based on start/limit
//    TODO -> cache this count somehow (group object...)
if ($searchText != ""){
        $groupCount =  dbGetGroupSubscribers($dbo, $table, $group_id, 'count', NULL, 'ASC', NULL, NULL, $searchText);
}
else{
        $groupCount =  dbGetGroupSubscribers($dbo, $table, $group_id, 'count');
}

// Instantiate Pager class (Using modified template from author)
$p = new Pager($appendUrl);
$start = $p->findStart($limit);
$pages = $p->findPages($groupCount, $limit);
// $pagelist : echo to print page navigation. -- TODO: adding appendURL to every link gets VERY LONG!!! come up w/ new plan!
$pagelist = $p->pageList($_GET['page'], $pages);

// get the subscribers array
if ($groupCount) {
	$subscribers = & dbGetSubscriber($dbo, dbGetGroupSubscribers($dbo, $table, $group_id,'list', $order, $orderType, $limit, $start, $searchText),'detailed', $table);
}
else {
	$groupCount = 0;
	$subscribers = array();
}
	
/**********************************
	SETUP TEMPLATE, PAGE
 *********************************/
$smarty = & bmSmartyInit();
$smarty->assign('returnStr', _T('Subscribers Page'));

$smarty->assign('fields', $fields);
$smarty->assign('groups',$groups);
$smarty->assign('table',$table);
$smarty->assign('group_id',$group_id);
$smarty->assign('limit',$limit);
$smarty->assign('order',$order);
$smarty->assign('orderType',$orderType);
$smarty->assign('searchText',$searchText);
$smarty->assign('subscribers',$subscribers);
$smarty->assign('pagelist',$pagelist);
$smarty->assign('groupCount',$groupCount);
if (defined('bm_ParentEmailField')){
    $smarty->assign('parentEmailFieldID',dbGetFieldId(bm_ParentEmailField));
}
if (defined('bm_PasswordField')){
    $smarty->assign('passwordFieldID',dbGetFieldId(bm_PasswordField));
}

$smarty->display('admin/subscribers/subscribers_manage.tpl');
bmKill();
?>