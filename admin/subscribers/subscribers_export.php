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

define('_IS_VALID', TRUE);
 
require('../../bootstrap.php');
require_once (bm_baseDir.'/inc/db_subscribers.php');
require_once (bm_baseDir.'/inc/db_fields.php');
$poMMo =& fireup("secure");
$dbo = & $poMMo->_dbo;

$fields = & dbGetFields($dbo);
$table = (empty ($_REQUEST['table'])) ? 'subscribers' : str2db($_REQUEST['table']);
$group_id = (empty ($_REQUEST['group_id'])) ? 'all' : str2db($_REQUEST['group_id']);
$order = (empty ($_REQUEST['order'])) ? 'email' : str2db($_REQUEST['order']);
$orderType = (empty ($_REQUEST['orderType'])) ? 'ASC' : str2db($_REQUEST['orderType']);
$searchText = (empty ($_REQUEST['searchText'])) ? '' : str2db($_REQUEST['searchText']);

if ($group_id == 'all' || is_numeric($group_id)){
	require_once (bm_baseDir.'/inc/db_sqlgen.php');
	$subscribers = & dbGetSubscriber($dbo, dbGetGroupSubscribers($dbo, $table, $group_id,'list', $order, $orderType, NULL, NULL, $searchText),'detailed', $table);
}
else
	bmKill('Bad group sent to export');

$encaser = "\"";
$delim = ", ";
$newline = "\n";
$carriage_return = "\r";
$empty = "\"\"";

$csv_output = "";
if (defined('bm_exportWithSubscriberID') && bm_exportWithSubscriberID){
        $csv_output.= $encaser."subscriber_id".$encaser.$delim;
}
$csv_output.= $encaser."email".$encaser.$delim;
foreach ( array_keys($fields) as $field_id ) {
  $csv_output .= $encaser.addslashes($fields[$field_id]['name']).$encaser.$delim;
}
$csv_output .= $encaser."date".$encaser.$newline;


foreach (array_keys($subscribers) as $subscriber_id) {
	$subscriber =& $subscribers[$subscriber_id];
	
        if (defined('bm_exportWithSubscriberID') && bm_exportWithSubscriberID){
		$csv_output .= $encaser.$subscriber_id.$encaser.$delim;
        }
	if (empty($subscriber['email']))
		$csv_output .= $empty.$delim;
	else
		$csv_output .= $encaser.$subscriber['email'].$encaser.$delim;
	foreach ( array_keys($fields) as $field_id) {
                if ($fields[$field_id]['type'] == 'bigtext'){
			if (empty($subscriber['bigdata'][$field_id]))
				$csv_output .= $empty.$delim;
			else{
                                $bigdata = $subscriber['bigdata'][$field_id];
                                $bigdata = preg_replace("/($newline)/","\\n",$bigdata);
                                $bigdata = preg_replace("/($carriage_return)/","\\r",$bigdata);
				$csv_output .= $encaser.$bigdata.$encaser.$delim;
                        }
                }
                else{
			if (empty($subscriber['data'][$field_id]))
				$csv_output .= $empty.$delim;
			else
				$csv_output .= $encaser.$subscriber['data'][$field_id].$encaser.$delim;
                }
	}
	if (empty($subscriber['date']))
		$csv_output .= $empty.$delim;
	else
		$csv_output .= $encaser.$subscriber['date'].$encaser.$newline;
}

$size_in_bytes = strlen($csv_output);
header("Content-disposition:  attachment; filename=subscribers_" .
date("Y-m-d").".csv; size=$size_in_bytes");

print $csv_output;
exit;  
 ?>