{include file="admin/inc.header.tpl"}

</div>
<!-- wide layout -->

{literal}
<style>
.bg1 {
	background-color: #b7cfec;
}

.bg2 {
	background-color: #87addc;
}
</style>
{/literal}


<div style="width:90%;">
	<span style="float: right; margin-right: 30px;">
		<a href="admin_subscribers.php{"?"|bmAppendPoMMoInstance}">{t 1=$returnStr}Return to %1{/t}</a>
	</span>
	<span style="float: right; margin-right: 30px;">
		<a href="subscribers_import.php{"?"|bmAppendPoMMoInstance}">{t}Upload a different file{/t}</a> 
	</span>
</div>

<p style="clear: both;"></p>
<hr>

<div align="center">
	
{if $page == 'preview'}
	
	<div style="width: 60%">
		<h2>{t}Preview Import{/t}</h2>
		<br>
		{t escape=no 1="<strong>`$totalImported`</strong>" 2="<strong>`$totalInvalid`</strong>" 3="<strong>`$totalDuplicate`</strong>"}%1 Valid subscribers were found.<br />%2 Invalid subscribers were found (will be imported and flagged).<br />%3 Duplicates were found.{/t}
		
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2">{$confirm.msg}</td>
	</tr>
		
	<tr>
		<td>
                        {if $confirm.dupesurl != ''}
			<p>	
				<a href="{$confirm.yesurl|bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/ok.png" class="navimage">
				{t}Yes{/t}</a> {t}Import, ignoring duplicates.{/t}
			</p>

			<p>	
				<a href="{$confirm.dupesurl}bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/ok.png" class="navimage">
				{t}Yes{/t}</a> {t}Import, updating duplicates.{/t}
			</p>
                        {else}
			<p>	
				<a href="{$confirm.yesurl|bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/ok.png" class="navimage">
				{t}Yes{/t}</a> {t}Import{/t}
			</p>
                        {/if}

			<p>
				<a href="{$confirm.nourl|bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/undo.png" class="navimage" align="middle">
				{t}No{/t}</a> {t}Cancel Import.{/t}
			</p>
		  </td>
		</tr>
		</table>
		
		{if $messages}
    	<div class="msgdisplay" style="text-align: left;">
    	{foreach from=$messages item=msg}
    	<div>* {$msg}</div>
    	{/foreach}
    	</div>
 		{/if}
		
	</div>

{elseif $page == 'import'}

	<div style="width: 60%">
		<h2>{t}Import Complete!{/t}</h2>
		<br>
		
		<a href="{$url.base}admin/subscribers/admin_subscribers.php{"?"|bmAppendPoMMoInstance}">
		<img src="{$url.theme.shared}images/icons/back.png" align="middle" class="navimage" border='0'>
		{t 1=$returnStr}Return to %1{/t}</a>
	
{elseif $page == 'assign'}
	<div style="width: 60%">
		<h2>{t}Upload Success{/t}</h2>
		<br>
		{t escape=no 1='<strong>' 2='</strong>'}Optionally, you may match the values to a subscriber field. If an imported subscriber is missing a value for a required field, they will be %1 flagged %2 to update their information.{/t}
	</div>
	
	<form method="POST" action="">
		<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
                {if $idField !== ""}
		<input type="hidden" name="field[{$idField}]" value="subscriber_id">
                {/if}
	
		<br>
		<table cellspacing="0" cellpadding="7">
			<tr>
				<td></td>
			{section name="fieldloop" start=1 loop=$numFields}
				<td class="{cycle values="bg1,bg2"}">
					{t 1=$smarty.section.fieldloop.index}Field #%1{/t}
				</td>
			{/section}
			</tr>
				<td>
					line #
				</td>
			{section name="field" start=0 loop=$numFields}
		            {if $smarty.section.field.index !== $idField}
				<td class="{cycle values="bg1,bg2"}">
					{if $smarty.section.field.index == $emailField}
					<i>email</i><input type="hidden" name="field[{$smarty.section.field.index}]" value="email">
					{else}
					<SELECT name="field[{$smarty.section.field.index}]">
						<option value="ignore">{t}Ignore Field{/t}</option>
						<option value="ignore">----------------</option>
					{foreach from=$fields key=key item=item}
						<option value="{$key}" {if $csvArray.fieldAssign.$key == $smarty.section.field.index}selected{/if}>{$item.name}</option>
					{/foreach}
					</SELECT>
					{/if}
				</td>
		            {/if}
			{/section}
			</tr>
			{* output from file now... *}
                        {foreach from=$csvArrayDisplay key=key item=item}
			<tr>
				<td style="border-right: thin dotted #000000;">
					{$key+1}
				</td>
				{section name="field" start=0 loop=$numFields}
		                    {if $smarty.section.field.index !== $idField}
					<td style="border-right: thin dotted #000000;">
						{$item[$smarty.section.field.index]}&nbsp;
					</td>
                                    {/if}
				{/section}
			</tr>
                        {/foreach}
                        
			<tr style="height: 2px;">
				<td style="border-right: thin dotted #000000; height: 2px;"></td>
				<td colspan="*" bgcolor="#000000" style="height: 2px;"></td>
			</tr>
		</table>
	
		<br>
		
		{t 1=$csvArray.csvFile|@count}%1 subscribers to import.{/t}
		<br>
		<img src="{$url.theme.shared}images/icons/download.png"><br>
		<input type="submit" name="preview" value="{t}Click to Preview{/t}">

	</form>
{/if}


</div>
{include file="admin/inc.footer.tpl"}