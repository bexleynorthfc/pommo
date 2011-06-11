<?php
	$tqpDisplayHead = false;

	$p = $Bootstrap->usePackage('poMMo');
	
	if ($_GET['sid'] == ""){
		$pommo_url = "admin_subscribers.php?package=".$_REQUEST['package'];
	}
	else{
		$pommo_url = "subscribers_mod.php?package=".$_REQUEST['package']."&amp;table=subscribers&amp;action=edit&amp;sid=".$_GET['sid'];
	}
?>
<iframe id="pommo_frame" src="<?php echo $p->getPackageURL(); ?>admin/subscribers/<?php echo $pommo_url; ?>" width="100%" frameborder="0" marginheight="0" marginwidth="0">
  <p>Your browser does not support iframes.</p>
</iframe>
<script type="text/javascript">
function resizeIframe() {
    var height = document.documentElement.clientHeight;
    height -= document.getElementById('pommo_frame').offsetTop;
    
    // not sure how to get this dynamically
    height -= 50; /* whatever you set your body bottom margin/padding to be */
    
    document.getElementById('pommo_frame').style.height = height +"px";
    
};
document.getElementById('pommo_frame').onload = resizeIframe;
window.onresize = resizeIframe;
</script>
