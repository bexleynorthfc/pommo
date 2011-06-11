{* Include form CSS styling *}
<link href="{$url.theme.this}inc/subscribe_form.css" type="text/css" rel="STYLESHEET">

{literal}
<style>
	#subscribeForm .prompt {
		width: 35%;
		text-align: right;
                vertical-align: top;
	}
</style>
{/literal}

<div id="subscribeForm" class="subscribeForm-{$poMMo_package->package_name}">

<form action="" method="POST">
<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
<input type="hidden" name="updateForm" value="true">
<input type="hidden" name="original_email" value="{$original_email}">
{if $usingPassword}
<input type="hidden" name="passcode" value="{$passcode}">
{/if}
{if $subscriber_id}
<input type="hidden" name="subscriber_id" value="{$subscriber_id}">
{/if}


	<fieldset style="width: 75%; margin: 0px; padding: 0px;">
		<legend>{t}Your Information{/t}</legend>
	
{if $usingPassword}
<input type="hidden" name="d[{$passwordFieldID}]" value="{$passcode}">
{/if}
{if $current_id} {* if editing one of many records who use the same email address *}
<input type="hidden" name="current_id" value="{$current_id}">
{/if}

	<table id="stripeMe" border="0" width="100%" cellspacing="0" cellpadding="3">
	    
{if $ParentID eq '' or $current_id eq $ParentID}
	<tr>
		<td class="prompt">
			<label class="required">{t}Your Email:{/t}</label>
		</td>
		<td>
			<input type="text" class="text" size="32" maxlength="60" name="bm_email" id="bm_email"
			  value="{$bm_email}">
		</td>
	</tr>
		
	<tr>
		<td class="prompt">
			<label class="required">{t}Verify Email:{/t}</label>
		</td>
		<td>
			<input type="text" class="text" size="32" maxlength="60" name="email2" id="email2"
			value="{$email2}">
		</td>
	</tr>
{else}	
	<tr>
		<td class="prompt">
			<label class="required">{t}Your Email:{/t}</label>
		</td>
		<td>
		    <p>{$bm_email}</p>
		</td>
	</tr>
	<input type="hidden" name="bm_email" id="bm_email" value="{$bm_email}">
	<input type="hidden" name="email2" id="email2" value="{$email2}">
{/if}

	{foreach name=demos from=$fields key=key item=demo}
	<tr id="pommo-row-{$key}">
		<td class="prompt">
			<label {if $demo.required == "on"}class="required"{/if}>{$demo.prompt}</label>
		</td>
		<td>
			{if $demo.type == 'text'}
				<input type="text" class="text" size="32" name="d[{$key}]" id="d[{$key}]" 
				{if isset($d.$key)}value="{$d.$key}"{elseif $demo.normally}value="{$demo.normally}"{/if}>
					
			{elseif $demo.type == 'checkbox'}
				<input type="hidden" name="chkSubmitted" value="TRUE">
				<input type="checkbox" name="d[{$key}]" id="d[{$key}]"
				{if $d.$key == "on"}checked{elseif !isset($chkSubmitted) && $demo.normally == "on"}checked{/if}>
					
			{elseif $demo.type == 'multiple'}
				<select name="d[{$key}]" id="d[{$key}]">
						<option value="">{t}Choose Selection{/t}</option>
					{foreach from=$demo.options item=option}
   						<option {if $d.$key == $option}SELECTED{elseif !isset($d.$key) && $demo.normally == $option}SELECTED{/if}>{$option}</option>
   					{/foreach}
   				</select>
   					
			{elseif $demo.type == 'multiplemultiple'}
                                {foreach name=options from=$demo.options item=option}
                                        {assign var="iteration" value=$smarty.foreach.options.iteration}
                                       <input type='checkbox' {if isset($choices.$key.$iteration)}checked {/if}name="choices[{$key}][{$smarty.foreach.options.iteration}]" value="{$option}"> {$option}<br />
                                {/foreach}
   					
			{elseif $demo.type == 'bigtext'}
                                <textarea class="textarea" rows="5" cols="40" name="d[{$key}]" id="d[{$key}]">{if isset($bd.$key)}{$bd.$key}{elseif isset($d.$key)}{$d.$key}{elseif $demo.normally}{$demo.normally}{/if}</textarea>
					
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
	<input class="button" type="submit" name="update" value="{t}Update Records{/t}" />
</div>
		
</form>
</div>
