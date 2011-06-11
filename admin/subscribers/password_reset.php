<?php
	define('_IS_VALID', TRUE);

	require('../../bootstrap.php');
	require_once (bm_baseDir.'/inc/db_subscribers.php');

	$poMMo = & fireup('secure');
	$logger = & $poMMo->_logger;
	$dbo = & $poMMo->_dbo;

	$subscriber = dbGetSubscriber($dbo,$_GET['sid']);
	if (is_array($subscriber)){
		$subscriber = current($subscriber);
	    require_once (bm_baseDir . '/inc/lib.mailings.php');
	    bmSendConfirmation($subscriber['email'], getPasswordCode($dbo,$subscriber['email']), "password");
		echo "success";
	}
	else{
		echo "Something went wrong";
	}
?>