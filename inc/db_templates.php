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
 * Don't allow direct access to this file. Must be called from elsewhere
 */
defined('_IS_VALID') or die('Move along...');

// Cool DB Query Wrapper from Monte Ohrt
require_once (bm_baseDir.'/inc/safesql/SafeSQL.class.php');


/* Get the number of mailings in the table mailing_templates of the database */
function & dbGetTemplatesCount(& $dbo) {
	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT count(id) FROM %s ", array($dbo->table['mailing_templates']) );
	$count = $dbo->query($sql,0); // note, this will return "false" if no row returned -- though count always returns 0 (mySQL)!
	return ($count) ? $count : 0;
} //dbGetTemplatesCount


/* Get the mailings templates matrix */
function & dbGetLimitedTemplates(& $dbo, $start, $limit, $order, $orderType) {

	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT id, name FROM %s ORDER BY %s %s LIMIT %s, %s ", 
		array($dbo->table['mailing_templates'], $order, $orderType, $start, $limit) );

	$templates = array();
	
	while ($row = $dbo->getRows($sql)) {
	 		
	 		$templates[] = array(
	 			'templateid' => $row['id'],
	 			'name' => $row['name']
	 		);
	 }
	return $templates;

} //dbGetLimitedTemplates

function & dbGetAllTemplates(& $dbo, $order = 'name', $orderType = 'ASC') {

	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT id, name FROM %s ORDER BY %s %s ", 
		array($dbo->table['mailing_templates'], $order, $orderType) );

	$templates = array();
	
	while ($row = $dbo->getRows($sql)) {
	 		
	 		$templates[] = array(
	 			'templateid' => $row['id'],
	 			'name' => $row['name']
	 		);
	 }
	return $templates;

} //dbGetAllTemplates

function & dbAddTemplate(& $dbo,$template){	
	$safesql =& new SafeSQL_MySQL;
	$query = 'INSERT INTO %s SET name="%s", body="%s"';
	$sql = $safesql->query($query,array($dbo->table['mailing_templates'],$template['name'],$template['body']));
	return $dbo->affected($sql);
}

function dbUpdateTemplate(& $dbo,$template){	
	$safesql =& new SafeSQL_MySQL;
	$query = 'UPDATE %s SET name="%s", body="%s" WHERE id=%s';
	$sql = $safesql->query($query,array($dbo->table['mailing_templates'],$template['name'],$template['body'],$template['id']));
	$dbo->query($sql);
	if ($dbo->getError() == ""){
		return true;
	}
	else{
		return false;
	}
}

// Get Infos on a Template from a Array or numeric ID Information
function & dbGetTemplateInfo(& $dbo, $selid) {
	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT id, name, body, charset FROM %s WHERE id IN (%q)", 
		array($dbo->table['mailing_templates'], $selid) );

	
	$templates = $dbo->getAll($sql);

	return $templates;
} //dbGetTemplateInfo

// Get Info on a Template from a numeric ID Information
function & dbGetTemplate(& $dbo, $selid) {
	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT id, name, body, charset FROM %s WHERE id = %s", 
		array($dbo->table['mailing_templates'], $selid) );
	
	$template = $dbo->getAll($sql);
	
	if (count($template)){
		return current($template);
	}
	else{
		return null;
	}
} //dbGetTemplateInfo

// Retrieves the Template id (if exists) based on the name
function dbGetTemplateID(& $dbo,$name){
	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("SELECT id FROM %s WHERE name = '%s'", 
		array($dbo->table['mailing_templates'], $name) );
	
	$template = $dbo->getAll($sql);
	
	if (count($template)){
		return current($template);
	}
	else{
		return null;
	}
} // dbGetTemplateID



// Removes one or more data records from the mailing_templates table
// $delid can be numeric oder a Array
function & dbRemoveTemplates(& $dbo, $delid) {
	
	if (empty($delid))
		return false;
	// NOTE; not necessary to check if delid is an array, as safeSQL %q
	// will automatically convert to one... & SQL 'IN' can take 1 param.
		
	// delete array of mails from mailing_templates table
	$safesql =& new SafeSQL_MySQL;
	$sql = $safesql->query("DELETE FROM %s WHERE id IN (%q) ", array($dbo->table['mailing_templates'], $delid) );
	$dbo->query($sql);

	return true;
} //dbRemoveTemplate
