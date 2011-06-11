{* Include form CSS styling *}
<form method="post" style='margin: 3px 5px 0 0' action="{$url.base}user/subscribe.php">
<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
Email: <input type="text" name="bm_email" id="bm_email" value="Your Email" onBlur="javascript:if(document.getElementById('bm_email').value=='')document.getElementById('bm_email').value='Your Email';" onFocus="javascript:if(document.getElementById('bm_email').value=='Your Email')document.getElementById('bm_email').value='';"> <input type='submit' value='Subscribe'>
<input type="hidden" name="bmReferer" value="{$referer}">
</form>
