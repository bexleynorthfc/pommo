<div id="pommo_header"><h3>{t}{$smarty.const.bm_SubscriberWord} Update{/t}</h3></div>

{if $Family|is_array}
<form action="" method="POST" name="switch_form" id="switch_form">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
<p>There are multiple {$smarty.const.bm_SubscriberWord}s associated with this email address.  You can switch which record you are editing by changing the selection here.
<select name='subscriber_id' onchange='javascript:document.switch_form.submit();'>
{foreach from=$Family item=Subscriber}
    <option value='{$Subscriber.id}' {if $Subscriber.id eq $current_id}SELECTED {assign var='CurrentName' value=$Subscriber.data.FullName}{/if}>{$Subscriber.data.FullName}</option>
{/foreach}
{if $subscriber_id eq 'new'}
    <option value='' SELECTED>&lt;New {$smarty.const.bm_SubscriberWord}&gt;</option>
{/if}
</select>
<noscript><input type="submit" name='Switch' value='Switch'></noscript>
</form>
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
 		<h2 style='margin-bottom:0px'>There are Errors:</h2>
    	<div class="errdisplay" style='margin-bottom:10px;'>
    	{foreach from=$errors item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 	{/if}
 	

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

{**********************************************************
    Subscribe Form Begin
**********************************************************}

<div id="subscribeForm" class="subscribeForm-{$poMMo_package->package_name}" style="margin-bottom:0px:">

<form action="" method="POST" name="update_form">
<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
<input type="hidden" name="updateForm" value="true">
<input type="hidden" name="update" value="true">
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
	    
{if ($ParentID eq '' or $current_id eq $ParentID) and $subscriber_id ne 'new'}
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
	{if $poMMo_package|is_a:'Package' and $poMMo_package|method_exists:'insertMarkup'}
		{assign var="special_field_display" value=$poMMo_package->insertMarkup($smarty_object,'special_field_display')}
	{else}
		{assign var="special_field_display" value=""}
	{/if}
	{if $special_field_display ne ''}
		{$special_field_display}
	{else}
	<tr id="pommo-row-{$key}">
		<td class="prompt">
			<label {if $demo.required == "on"}class="required"{/if}>{$demo.prompt}</label>
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
   	{/if}
   	{/foreach}
	</table>
	
</fieldset>

<div style="margin-left: 5px; margin-top: 5px;">
	{t escape=no 1="<span class=\"required\">" 2="</span>"}Fields in %1bold%2 are required{/t}
</div>

<script type='text/javascript' language='javascript'>
<!--
// to enable changes to be sent when user hits enter
document.write("<input type='submit' style='display:none'/>");
-->
</script>
<noscript>
<div style="float:left;margin:0;">
	<input class="button" type="submit" name="update_btn" value="{t}Update Records{/t}" />
</div>
</noscript>
</form>
</div>

{**********************************************************
    Subscribe Form End
**********************************************************}
{literal}
<style type='text/css'>
.action_button{
    width:200px;
    margin-top:3px;
}
</style>
{/literal}

<script type='text/javascript'>
<!--
{if $Family|is_array and $current_id != $ParentID}
{literal}
    function confirmDelete(){
        if (confirm('Are you sure you want to delete {/literal}{$CurrentName}{literal}?  This action cannot be undone.' + "\n\n" + 'All other associated {/literal}{$smarty.const.bm_SubscriberWord}{literal}s will remain unaffected.')){
	// IE7 kacks if you call this document.action_form.delete.value.  Dunno why.
            document.getElementById('delete').value='delete';
            document.action_form.submit();
        }
    }
{/literal}
{/if}
{literal}
    function confirmUnsubscribe(){
        if (confirm('Are you sure you want to delete yourself {/literal}{if $Family|is_array}and all associated {$smarty.const.bm_SubscriberWord}s{/if}{literal}?' + "\n\n" + 'You will be sent an email with instructions on how to complete this request.')){
            document.action_form.unsubscribe.value='unsubscribe';
            document.action_form.submit();
        }
    }

{/literal}
-->
</script>

<script type='text/javascript' language="javascript">
document.write("<h3 style='margin-bottom:0px'>Click...</h3>");
document.write("<input type='button' class='action_button' value='{t}Update Record{/t}' onclick='javascript:document.update_form.submit();'/> to save your changes");
{if $usingParentEmail}
{if $subscriber_id ne 'new'}
document.write("<br><input type='button' class='action_button' value='{t}Add New{/t}' onclick='javascript:document.add_new_form.submit();'/> to register a new {$smarty.const.bm_SubscriberWord} using this email address");
{else}
document.write("<br><input type='button' class='action_button' value='{t}Cancel New Add{/t}' onclick='javascript:document.cancel_new_form.submit();'> to cancel the new addition");
{/if}
{/if}
document.write("<br><input type='button' class='action_button' value='{t}Delete{/t}{if $Family|is_array} All{/if}' onclick='javascript:confirmUnsubscribe();'> to delete yourself{if $Family|is_array} and all associated {$smarty.const.bm_SubscriberWord}s{/if}");
{if $Family|is_array and $current_id != $ParentID and $subscriber_id ne 'new'}
document.write("<br><input type='button' class='action_button' value='{t}Delete {$CurrentName}{/t}' onclick='javascript:confirmDelete();'> to delete just {$CurrentName}");
{/if}
{if $poMMo_package|is_a:'Package' and $poMMo_package|method_exists:'insertMarkup'}
	{$poMMo_package->insertMarkup($smarty_object,'action_buttons')}
{/if}
document.write("<br><input type='button' class='action_button' value='{t}Logout{/t}' onclick='javascript:window.location.href=\"{$url.base}user/login.php{"?"|bmAppendPoMMoInstance}\";'> to logout");
</script>

{if $usingParentEmail}
<form action="" method="POST" name="add_new_form">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
    <input type='hidden' name='subscriber_id' value='new'>
    <input type='hidden' name='add_new' value='true'>
<noscript>    
    <p>To register another {$smarty.const.bm_SubscriberWord} under this email address <input type='submit' name='add_new_btn' value='Click Here'></p>
</noscript>    
</form>
<form action="" method="POST" name="cancel_new_form">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
</form>
{/if}
<div style="margin-top: 20px; margin-left: 30px;">
	<form action="" method="post" name="action_form">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<input type="hidden" name="original_email" value="{$original_email}">
	<input type="hidden" name="unsubscribe" value="">
	<input type="hidden" id="delete" name="delete" value="">
	<input type="hidden" name="bm_email" value="{$bm_email}">
	{if $usingPassword}
	<input type="hidden" name="passcode" value="{$passcode}">
	{/if}
	{if $subscriber_id}
	<input type="hidden" name="subscriber_id" value="{$subscriber_id}">
	{/if}
	<noscript>
	<input type="submit" name="unsubscribe" value="{t}Click to Delete{/t}{if $Family|is_array} Yourself and All Associated {$smarty.const.bm_SubscriberWord}s{/if}">
	{if $Family|is_array and $current_id != $ParentID and $subscriber_id ne 'new'}
	<input type="submit" name="delete" value="{t}Click to Delete {$CurrentName}{/t}" onclick='javascript:confirmDelete();'>
	{/if}
</noscript>
</form>
</div>

	

<noscript>
    <br><a href='{$url.base}user/login.php{"?"|bmAppendPoMMoInstance}'>Logout</a>
</noscript>
