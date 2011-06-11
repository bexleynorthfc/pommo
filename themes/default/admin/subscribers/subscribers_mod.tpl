{include file="admin/inc.header.tpl"}

</div>
<!-- end content -->

{assign var="mm_header_printed" value="no"}
{assign var="bt_header_printed" value="no"}
{foreach name=demo from=$fields key=demo_id item=demo}
        {if $demo.type == 'multiplemultiple'}
                {if $mm_header_printed == "no"}
                
<script src="{$url.theme.shared}js/multiplemultiple.js" type="text/javascript"></script>
                {assign var="mm_header_printed" value="yes"}
                {/if}
        {elseif $demo.type == 'bigtext'}
                {if $bt_header_printed == "no"}
                
<script src="{$url.theme.shared}js/bigtext.js" type="text/javascript"></script>
                {assign var="bt_header_printed" value="yes"}
                {/if}
        {/if}
{/foreach}

{if $mm_header_printed == "yes"}
<script type='text/JavaScript'>
<!--
        var AllOptions = new Array();
        {foreach name=demo from=$fields key=demo_id item=demo}
                {if $demo.type == 'multiplemultiple'}
        
        AllOptions[{$demo_id}] = "{","|implode:$demo.options}";
                {/if}
        {/foreach}

-->
</script>
{/if}
{if $passwordFieldID ne ''}
<script type='text/javascript'>
{literal}
function resetPassword(key){
	document.getElementById('password_reset_'+key).innerHTML = "Working...";
	if (window.XMLHttpRequest){// code for IE7+, Firefox, Chrome, Opera, Safari
		xmlhttp=new XMLHttpRequest();
	}
	else{// code for IE6, IE5
		xmlhttp=new ActiveXObject("Microsoft.XMLHTTP");
	}
	var url = "{/literal}{$url.http}{$url.base}{literal}admin/subscribers/password_reset.php?sid=" + key + "{/literal}{"&"|bmAppendPoMMoInstance}{literal}";
	xmlhttp.open("GET",url,false);
	xmlhttp.send(null);
	if (xmlhttp.responseText == 'success'){
		document.getElementById('password_reset_'+key).innerHTML = "Reset email sent";
	}
	else{
		document.getElementById('password_reset_'+key).innerHTML = xmlhttp.responseText;
	}
}
{/literal}
</script>
{/if}


<div style="width:90%;">
	{if $smarty.get.action eq 'create'}
	<span style="float: left; margin-left: 30px;">
    	<form name="bForm" id="bForm" method="POST" action="">
			<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
    	    <input type="hidden" name="bFormSubmitted" value="true">
    	<nobr>
    	{t}Add More Subscribers:{/t} 
    		<SELECT name="max_rows" onChange="document.bForm.submit()">
    			<option value="1"{if $max_rows == '1'} SELECTED{/if}>1</option>
    			<option value="5"{if $max_rows == '5'} SELECTED{/if}>5</option>
    			<option value="10"{if $max_rows == '10'} SELECTED{/if}>10</option>
    			<option value="20"{if $max_rows == '20'} SELECTED{/if}>20</option>
    			<option value="25"{if $max_rows == '25'} SELECTED{/if}>25</option>
    		</SELECT>
    	</nobr>
	    </form>
	</span>
	{/if}
	<span style="float: right; margin-right: 30px;">
		<a href="subscribers_manage.php{"?"|bmAppendPoMMoInstance}">{t 1=$returnStr}Return to %1{/t}</a>
	</span>
</div>

<p style="clear: both;"></p>
<hr>

{if $errors}
<div class="errdisplay">
        {foreach from=$errors item=msg}
        <div>* {$msg}</div>
        {/foreach}
</div>
{/if}


<form method="POST" action="" name="UpdateForm">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<input type="hidden" name="order" value="{$order}">
	<input type="hidden" name="orderType" value="{$orderType}">
	<input type="hidden" name="limit" value="{$limit}">
	<input type="hidden" name="table" value="{$table}">
	<input type="hidden" name="group_id" value="{$group_id}">
	<input type="hidden" name="action" value="{$action}">
        {foreach from=$sid item=id}
	<input type="hidden" name="sid[]" value="{$id}">
        {/foreach}

	{if $action == 'edit' || $action == 'create' }
		<table cellspacing="5" border="0" style="text-align:left;">
		<tr>
			<td nowrap>{t}email{/t}</td>
			
			{foreach from=$fields key=key item=item}
			    {if $passwordFieldID ne $key or true}
				<td nowrap>{$item.name}</td>
				{/if}
			{/foreach}
		
		</tr>
		
		
		{foreach name=sub from=$subscribers key=key item=item}
		<tr>
			
			<input type="hidden" name="editId[]" value="{$key}">
			<input type="hidden" name="date[{$key}]" value="{$item.date}">
			<input type="hidden" name="oldEmail[{$key}]" value="{$item.old_email}">
			{if $passwordFieldID ne ''}
			<input type="hidden" name="d[{$key}][{$passwordFieldID}]" value="{$item.data.$passwordFieldID}">
			{/if}
			
			<td nowrap>
			{if $parentEmailFieldID ne '' and $item.email eq 'none' and $item.data.$parentEmailFieldID ne '' and false}
			    {$item.data.$parentEmailFieldID}
				<input type="hidden" name="email[{$key}]" value="{$item.email}">
				<input type="hidden" name="d[{$key}][{$parentEmailFieldID}]" value="{$item.data.$parentEmailFieldID}">
			{else}
				<input type="text" name="email[{$key}]" value="{$item.email}" maxlength="60">
			{/if}
			</td>
			
			{foreach name=demo from=$fields key=demo_id item=demo}
		        {if $passwordFieldID eq $demo_id}
				<td nowrap id="password_reset_{$key}">
					{if $smarty.get.action ne 'create' and ($parentEmailFieldID eq '' or $item.email ne 'none' or $item.data.$parentEmailFieldID eq '')}
					<a href="javascript:resetPassword({$key});" >Send Reset Email</a>
					{else}
					&nbsp;
					{/if}
				</td>
				{else}
				<td nowrap>
				{if $demo.type == 'text'}
					<input type="text" name="d[{$key}][{$demo_id}]" maxlength="60" value="{$item.data.$demo_id|escape}">
				{elseif $demo.type == 'checkbox'}
					<input type="checkbox" name="d[{$key}][{$demo_id}]"{if $item.data.$demo_id == 'on'} checked{/if}>
				{elseif $demo.type == 'multiple'}
					<select name="d[{$key}][{$demo_id}]">
					<option> </option>
						{foreach name=option from=$demo.options item=option}
						<option{if $item.data.$demo_id == $option} SELECTED{/if}>{$option}</option>
						{/foreach}
                                        </select>
                                {elseif $demo.type == 'multiplemultiple'}
                                        <div id='{$key}_{$demo_id}' style='display: inline;'>
                                        <div id='{$key}_{$demo_id}_Choice' style='border: 1px solid black; background: white; padding: 10px; display:none; position: absolute'>
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:SaveList('{$demo_id}','{$key}');" value="Save">
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:RestoreList('{$demo_id}','{$key}');" value="Cancel">
                                        <br />
                                        {foreach name=option from=$demo.options item=option}
                                        <input type='checkbox' name='choice{$key}_{$demo_id}' value="{$option}" /> {$option}<br />
                                        {/foreach}
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:SaveList('{$demo_id}','{$key}');" value="Save">
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:RestoreList('{$demo_id}','{$key}');" value="Cancel">
                                        </div>
                                        {if $item.data.$demo_id == ""}
                                                {assign var="chosenString" value="(Click to Choose)"}
                                        {else}
                                                {assign var="chosenString" value=$item.data.$demo_id|truncate:60:"...":true}
                                        {/if}
                                        
                                        <input type=button class="ButtonSpoofingText" style='width: 100%;' id='{$key}_{$demo_id}_Input' onClick="javascript:ShowList('{$demo_id}','{$key}')" value="{$chosenString}">
                                        <input type='hidden' name="d[{$key}][{$demo_id}]" id="d[{$key}][{$demo_id}]" value="{$item.data.$demo_id|escape}"  />
                                        </div>
                                {elseif $demo.type == 'bigtext'}
                                        <div id='{$key}_{$demo_id}' style='display: inline;'>
                                        <div id='{$key}_{$demo_id}_Text' style='border: 1px solid black; background: white; padding: 10px; display:none; position: absolute;'>
                                        <textarea class="textarea" id="text{$key}_{$demo_id}" rows="12"></textarea>
                                        <br />
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:SaveText('{$demo_id}','{$key}');" value="Save">
                                        <input type=button class="ButtonSpoofingText" onClick="javascript:RestoreText('{$demo_id}','{$key}');" value="Cancel">
                                        </div>
                                        <input type='hidden' name="d[{$key}][{$demo_id}]" id="d[{$key}][{$demo_id}]" value="{$item.bigdata.$demo_id|escape}"  />
                                        {if $item.data.$demo_id == ""}
                                                {assign var="bigTextString" value="(Click to Edit)"}
                                        {else}
                                                {assign var="bigTextString" value=$item.data.$demo_id}
                                        {/if}
                                        <input type=button class="ButtonSpoofingText" style='width: 100%;' id='{$key}_{$demo_id}_Input' onClick="javascript:ShowText('{$demo_id}','{$key}')" value="{$bigTextString|truncate:25|escape}">
                                        </div>
				{else}
	   				{t}Unsupported Field Type.{/t}
	   			{/if}
				</td>
				{/if}
			{/foreach}
		</tr>
		{/foreach}
		</table>
		
		<br>
	        {if $action == 'create' }
		<input type="submit" name="submit" value="{t}Add{/t}">
                {else}
		<input type="submit" name="submit" value="{t}Update{/t}">
                {/if}
		<br>
	
	{elseif $action == 'delete'}
	
		{t}The following will be deleted{/t}
		
		<div style="float: right; width: 50%;">
			<input type="submit" name="submit" value="{t}Click to Delete{/t}">
		</div>
		<ul>
		{foreach from=$emails item=email}
			<input type="hidden" name="deleteEmails[]" value="{$email.email}">
			<li>{$email.display}</li>	
		{/foreach}
		{foreach from=$ids item=id}
			<input type="hidden" name="deleteIDs[]" value="{$id.id}">
			<li>{$id.display}</li>	
		{/foreach}
		</ul>
	
	{elseif $action == 'add'}
	
		{t}The following will be added as subscribers{/t}
		
		<span style="float: right; width: 50%;">
			<input type="submit" name="submit" value="{t}Click to Add{/t}">
		</span>
		<ul>
		{foreach from=$emails item=email}
			<input type="hidden" name="addEmails[]" value="{$email}">
			<li>{$email}</li>	
		{/foreach}
		</ul>
	
	{/if}
</form>

	
{include file="admin/inc.footer.tpl"}