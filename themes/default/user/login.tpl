<div id="pommo_header"><h3>{t}{$smarty.const.bm_SubscriberWord} Registration{/t}</h3></div>

	{if $poMMo_package|is_a:'Package' and $poMMo_package|method_exists:'getMessage'}
		<p>{$poMMo_package->getMessage('registration')}</p>
	{/if}
	
    {if $messages}
    	<div class="msgdisplay" style='margin-top:15px;'>
    	<h3>Messages</h3>
    	{foreach from=$messages item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}

	<form action="" method="POST">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<fieldset>
		<legend>{t}Login{/t}</legend>
	
		<div class="field">
			<div class="error">{validate id="email" message=$formError.email}</div>
			<label for="Email"><span class="required">{t}Your Email:{/t} </span></label>
			<input type="text" class="text" size="32" maxlength="60"
			  name="Email" value="{$Email|escape}" id="email" />
{if $usingPassword}
			<label for="bm_password"><span class="required">{t}Your Password:{/t} </span></label>
			<input type="password" class="text" size="32" maxlength="60"
			  name="bm_password" value="" id="bm_password" />  {* Trevor Mills - if I call this field 'Password', then it gets filled in by something - strange *}
{/if}			  
			  <input style="margin-left: 30px;" type="submit" value="{t}Login{/t}" />
		</div>
		
	</fieldset>
{if $usingPassword}
		<p style='font-size:0.8em'><a href='{$url.base}user/password_reset.php{"?"|bmAppendPoMMoInstance}'>I need to reset my password</a></p>
{/if}			  
		<p style='font-size:0.8em'><a href='{$url.base}user/subscribe.php{"?"|bmAppendPoMMoInstance}'>I need to signup</a></p>
	</form>