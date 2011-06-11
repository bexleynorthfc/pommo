<div id="pommo_header"><h3>{t}Pending Changes{/t}</h3></div>

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

{if !$nodisplay}
<br>
<form action="" method="POST">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<div style="text-align: center;">
		<input type="submit" name="reconfirm" value="{t}Click to *send* another confirmation email{/t}">
                <br><br>
		<input style="margin-left: 40px;" type="submit" name="cancel" 
		value="{t}Click to *cancel* your pending request{/t}">
	</div>
</form>
{/if}


<br><a href='{$url.base}user/login.php{"?"|bmAppendPoMMoInstance}'>Return to the User Maintenance Login Page</a>
