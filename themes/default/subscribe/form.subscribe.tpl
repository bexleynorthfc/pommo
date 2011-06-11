{* Include form CSS styling *}
<link href="{$url.theme.this}inc/subscribe_form.css" type="text/css" rel="STYLESHEET">

{literal}
<style>
	#subscribeForm .prompt {
		width: 35%;
		text-align: right;
	}
</style>
{/literal}

<h3>{$config.list_name} {t}Registration{/t}</h3>

<div id="subscribeForm" class="subscribeForm-{$poMMo_package->package_name}">

<form action="{$url.base}user/process.php" method="POST">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	{if $referer}
		<input type="hidden" name="bmReferer" value="{$referer}">
	{/if}
	
	<fieldset style="width: 75%; margin: 10px; padding: 10px;">
		<legend>{t}Your Information{/t}</legend>
	
	<table id="stripeMe" border="0" width="100%" cellspacing="0" cellpadding="3">
	<tr>
		<td class="prompt">
			<label class="required">{t}Your Email:{/t}</label>
		</td>
		<td>
			<input type="text" class="text" size="32" maxlength="60" name="bm_email" id="bm_email"
			  value="{$bm_email|escape}">
		</td>
	</tr>
{if $usingPassword}	
	<tr>
		<td class="prompt">
			<label class="required">{t}Choose a Password:{/t}</label>
		</td>
		<td>
			<input type="password" class="text" size="32" maxlength="60" name="bm_password" id="bm_password"
			  value="">
		</td>
	</tr>
	<tr>
		<td class="prompt">
			<label class="required">{t}Confirm Password:{/t}</label>
		</td>
		<td>
			<input type="password" class="text" size="32" maxlength="60" name="bm_password2" id="bm_password2"
			  value="">
		</td>
	</tr>
{/if}	
	<tr>
		<td colspan="2">
		    <hr>
		</td>
	</tr>
		
	{foreach name=demos from=$fields key=key item=demo}
	<tr id="pommo-row-{$key}">
		<td class="prompt" style="vertical-align: top">
			<label {if $demo.required == "on"}class="required"{/if}>{$demo.prompt}:</label>
		</td>
		<td>
			{if $demo.type == 'text'}
				<input type="text" class="text" size="32" name="d[{$key}]" id="d[{$key}]" 
				{if isset($d.$key)}value="{$d.$key|escape}"{elseif $demo.normally}value="{$demo.normally|escape}"{/if}>
					
			{elseif $demo.type == 'checkbox'}
				<input type="hidden" name="chkSubmitted" value="TRUE">
				<input type="checkbox" name="d[{$key}]" id="d[{$key}]"
				{if $d.$key == "on"}checked{elseif !isset($chkSubmitted) && $demo.normally == "on"}checked{/if}>
					
			{elseif $demo.type == 'multiple'}
				<select name="d[{$key}]" id="d[{$key}]">
						<option value="">{t}Choose Selection{/t}</option>
					{foreach from=$demo.options item=option}
   						<option {if $d.$key == $option}SELECTED{elseif !isset($submit) && $demo.normally == $option}SELECTED{/if}>{$option}</option>
   					{/foreach}
   				</select>
   					
			{elseif $demo.type == 'multiplemultiple'}       
                                {foreach name=options from=$demo.options item=option}
                                        {assign var="iteration" value=$smarty.foreach.options.iteration}
                                       <input type='checkbox' {if isset($choices.$key.$iteration)}checked {elseif !isset($submit) && $demo.normally == $option}checked {/if}name="choices[{$key}][{$smarty.foreach.options.iteration}]" value="{$option}"> {$option}<br />
                                {/foreach}
   					
			{elseif $demo.type == 'bigtext'}
                                <textarea class="textarea" rows="5" cols="40" name="d[{$key}]" id="d[{$key}]">{if isset($d.$key)}{$d.$key}{elseif $demo.normally}{$demo.normally}{/if}</textarea>
					
   			{else}
   				{t}Unsupported Field Type.{/t} 
   				
   			{/if}
   		</td>
   	</tr>
   	{/foreach}
	</table>
	
</fieldset>

<div style="margin-left: 5px; margin-top: 5px;">
	{t escape=no 1="<span class=\"required\">" 2="</span>"}Fields in %1bold%2 are required{/t}
</div>

<div style="margin-left: 15px; margin-top: 15px;">
	<input type="hidden" name="pommo_signup" value="true">
	<input class="button" type="submit" name="submit" value="{t}Register{/t}" />
</div>
		
</form>
</div>
