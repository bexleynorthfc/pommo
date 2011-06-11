<div id="pommo_header"><h3>{if $returning or code eq ''}Confirm Email{else}Reset Password{/if}</h3></div>

{if $hideform ne true}
    {if $code ne ""}
        <p>Your email address has been confirmed.</p>
        
        <p><strong>To continue</strong> the registration process, please <strong>choose a password</strong> for yourself.</p>
	{elseif $returning}
		{if $poMMo_package|is_a:'Package' and $poMMo_package|method_exists:'getMessage'}
			<p>{$poMMo_package->getMessage('returning')}</p>
		{/if}
	{else}
	    {t}To request a password reset, please enter your email address below.{/t}
	{/if}
{/if}
	
    {if $messages}
    	<div class="msgdisplay" style='margin-top:15px;'>
    	{foreach from=$messages item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}

{if $hideform ne true}
	<form action="" method="POST">
		<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<fieldset>
		<legend>{t}{/t}</legend>
	
		<div class="field">
	{if $code eq ""}
			<label for="Email"><span class="required">{t}Your Email:{/t} </span></label>
			<input type="text" class="text" size="32" maxlength="60"
			  name="Email" value="{$Email|escape}" id="email" />
	{else}
			<label for="bm_password"><span class="required">{t}New Password:{/t} </span></label>
			<input type="password" class="text" size="32" maxlength="60"
			  name="bm_password" value="" id="bm_password" />  {* Trevor Mills - if I call this field 'Password', then it gets filled in by something - strange *}
			<label for="bm_password2"><span class="required">{t}Confirm Password:{/t} </span></label>
			<input type="password" class="text" size="32" maxlength="60"
			  name="bm_password2" value="" id="bm_password2" />  
			<input type="hidden" name="code" value="{$code}">
			<input type="hidden" name="Email" value="{$Email|urldecode}">
			<input type="hidden" name="returning" value="{$returning}">
    {/if}			  
			  <input style="margin-left: 30px;" type="submit" value="{if $returning || $code eq ""}Confirm Email{else}Set Password{/if}" />
		</div>
		
	</fieldset>
	</form>
{/if}

<p>If you're having problems, please contact us at <a href='mailto:{$admin_email}'>{$admin_email}</a></p>

