<div id="pommo_header"><h3>{t}Registration Confirmation{/t}</h3></div>

{if $back}
<a href="{$referer|bmAppendPoMMoInstance}">
		{t website=$config.site_name}Back to Subscription Form{/t}</a>
{/if}

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
 		<h3>Your registration did not go through</h3>
    	<div class="errdisplay">
    	{foreach from=$errors item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}
 	
 	{if $dupe}
 		<div class="msgdisplay">
 		* To register someone else under an existing email address, <a href="{$login_url|bmAppendPoMMoInstance}">login</a> and click the "Add New" button at the bottom of the screen.
 		</div>
 	{/if}

