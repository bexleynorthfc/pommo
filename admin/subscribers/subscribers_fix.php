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
$field_id = 28;  //Local
//$field_id = 30;  //100Megs
 
$subscribers = dbGetSubscriber($dbo,'all');
$i = 0;
foreach (array_keys($subscribers) as $subscriber_id){
        set_time_limit(0);
        echo "<p>";
        if ($subscribers[$subscriber_id]['data'][$field_id] != ''){
                $subscribers[$subscriber_id]['newEmail'] = str_replace('___at___','@',$subscribers[$subscriber_id]['data'][$field_id]);
                dbSubscriberUpdate($dbo,$subscribers[$subscriber_id]);
        }
        $i++;
}

echo "all done $i subscribers";
?>