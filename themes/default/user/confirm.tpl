<div id="pommo_header"><h3>{t}{$smarty.const.bm_SubscriberWord} Registration Complete{/t}</h3></div>

<br>
    {if $messages}
    	<div class="msgdisplay">
    	{foreach from=$messages item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}
 	
 	{if $errors}
 		<br>
    	<div class="errdisplay">
    	{foreach from=$errors item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}
 	
 	<p>Proceed to <a href='{$url.base}user/login.php{"?"|bmAppendPoMMoInstance}'>login page</a>.</p>
