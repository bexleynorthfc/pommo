{include file="admin/inc.header.tpl"}

</div>
<!-- end content -->

<div style="width:90%;">
	<span style="float: right;">
		{if $table == 'subscribers'}
			<a href="subscribers_manage.php?table=pending{"&"|bmAppendPoMMoInstance}">{t}View Pending{/t}</a>
		{else}
			<a href="subscribers_manage.php?table=subscribers{"&"|bmAppendPoMMoInstance}">{t}View Subscribed{/t}</a>
		{/if}
	</span>
	<span style="float: right; margin-right: 30px;">
		<a href="{$smarty.const.CMS_ADMIN_URL}{$bootstrap->getAdminURL()}&amp;package=ImportExport&amp;{$smarty.const.ADMIN_PAGE_PARM}=export&amp;p={$poMMo_package->package_name}&amp;Group={if $group_id eq 'all'}All Subscribers{else}{$groups.$group_id}{/if}&amp;Table={$table}&amp;searchText={$searchText|urlencode}" target="_top">{t}Export to CSV{/t}</a>
	</span>
	<span style="float: right; margin-right: 30px;">
		<a href="subscribers_mod.php?action=create&table={$table}&limit={$limit}&order={$order}&orderType={$orderType}&group_id={$group_id}&searchText={$searchText}{"&"|bmAppendPoMMoInstance}">{t}Add a Subscriber{/t}</a>
	</span>
    <span style="float: right; margin-right: 30px;">
	{if $searchText ne '' and $table eq 'subscribers'}
    	<a href="../mailings/mailings_send.php?searchText={$searchText|urlencode}&group_id={$group_id}{"&"|bmAppendPoMMoInstance}">{t}Email Found {$smarty.const.bm_SubscriberWord}s{/t}</a>
	{else}
    	<a href="../mailings/mailings_send.php?group_id={$group_id}{"&"|bmAppendPoMMoInstance}">{t}Email This Group{/t}</a>
	{/if}
    </span>
	<span style="float: right; margin-right: 30px;">
		<a href="admin_subscribers.php{"?"|bmAppendPoMMoInstance}">{t 1=$returnStr}Return to %1{/t}</a>
	</span>
</div>

<p style="clear: both;"></p>
<hr>

<div style="text-align: center; width: 100%;" >
	
	<form name="bForm" id="bForm" method="POST" action="">
		<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	    <input type="hidden" name="bFormSubmitted" value="true">
	<nobr>
	{t}{$smarty.const.bm_SubscriberWord}s per Page:{/t} 
		<SELECT name="limit" onChange="document.bForm.submit()">
			<option value="10"{if $limit == '10'} SELECTED{/if}>10</option>
			<option value="50"{if $limit == '50'} SELECTED{/if}>50</option>
			<option value="150"{if $limit == '150'} SELECTED{/if}>150</option>
			<option value="300"{if $limit == '300'} SELECTED{/if}>300</option>
			<option value="500"{if $limit == '500'} SELECTED{/if}>500</option>
		</SELECT>
	</nobr>
	
	<span style="width: 30px;"></span>
	
	<nobr>
	{t}Belonging to Group:{/t} 
		<SELECT name="group_id" onChange="document.bForm.submit()">
			<option value=all>{t}All {$smarty.const.bm_SubscriberWord}s{/t}</option>
			{foreach from=$groups key=key item=item}
				<option value="{$key}"{if $group_id == $key} SELECTED{/if}>{$item}</option>
			{/foreach}
		</SELECT>
	</nobr>
	
	<span style="width: 30px;"></span>
	
	<nobr>
	{t}Order by:{/t}
		<SELECT name="order" onChange="document.bForm.submit()">
			<option value="email">{t}email{/t}</option>
			{foreach from=$fields key=key item=item}
				<option value="{$key}"{if $order == $key} SELECTED{/if}>{$item.name}</option>
			{/foreach}
		</SELECT>
	
	<span style="width: 15px;"></span>
	
	<SELECT name="orderType" onChange="document.bForm.submit()">
		<option value="ASC"{if $orderType == 'ASC'} SELECTED{/if}>{t}ascending{/t}</option>
		<option value="DESC"{if $orderType == 'DESC'} SELECTED{/if}>{t}descending{/t}</option>
	</SELECT>
	</nobr>
	
	<nobr>
	{t}Search:{/t}
                <input type='text' name='searchText' style='background: white;border-color: #7F9DB9' onChange="document.bForm.submit()" value='{$searchText}'>
	</nobr>
	
	</form>

<br><br>

(<em>{t 1=$groupCount}%1 {$smarty.const.bm_SubscriberWord}s{/t}</em>)

<form name="oForm" id="oForm" method="POST" action="subscribers_mod.php">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
	<input type="hidden" name="order" value="{$order}">
	<input type="hidden" name="orderType" value="{$orderType}">
	<input type="hidden" name="limit" value="{$limit}">
	<input type="hidden" name="table" value="{$table}">
	<input type="hidden" name="group_id" value="{$group_id}">

<table cellpadding="5" cellspacing="0" border="0" style="text-align:left;">
	<tr>
		<td nowrap>
			<b>{t}select{/t}</b>
		</td>
		<td nowrap>&nbsp;
		</td>
		<td>
			&nbsp;
		</td>
		<td nowrap>
			<b>{t}email{/t}{if $parentEmailFieldID} or (parent email){/if}</b>
		</td>
		
		{foreach from=$fields key=key item=item}
		    {if $parentEmailFieldID ne $key and $passwordFieldID ne $key}
			    <td nowrap><b>{$item.name}</b></td>
			{/if}
		{/foreach}
		
	</tr>
	
		
	{foreach name=sub from=$subscribers key=key item=item}
	<tr bgcolor="{cycle values="#eeeeee,"}">
		<td nowrap><input type="checkbox" name="sid[]" value="{$item.id}"></td>
		<td nowrap>
			{if $table == 'subscribers'}
				<a href="subscribers_mod.php?sid={$item.id}&action=edit&table={$table}&limit={$limit}&order={$order}&orderType={$orderType}&group_id={$group_id}{"&"|bmAppendPoMMoInstance}">{t}edit{/t}</a>
			{else}
				<a href="subscribers_mod.php?sid={$item.id}&action=add&table={$table}&limit={$limit}&order={$order}&orderType={$orderType}&group_id={$group_id}{"&"|bmAppendPoMMoInstance}">{t}add{/t}</a>
			{/if}
				</td>
		<td nowrap><a href="subscribers_mod.php?sid={$item.id}&action=delete&table={$table}&limit={$limit}&order={$order}&orderType={$orderType}&group_id={$group_id}{"&"|bmAppendPoMMoInstance}">{t}delete{/t}</a></td>
		{if $parentEmailFieldID ne '' and $item.email == 'none' and $item.data.$parentEmailFieldID ne ''}
		<td nowrap>(<strong>{$item.data.$parentEmailFieldID}</strong>) <a href='mailto:{$item.data.$parentEmailFieldID}'><img src="{$url.theme.shared}images/icons/emailicon.gif" class="navimage" border="0" align='baseline'/></a></td>
		{elseif $item.email ne 'none'}
		<td nowrap><strong>{$item.email}</strong> <a href='{$url.base}admin/mailings/mailings_send.php?searchText={$item.email|urlencode}&group_id=all{"&"|bmAppendPoMMoInstance}'><img src="{$url.theme.shared}images/icons/emailicon.gif" class="navimage" border="0" align='baseline'/></a></td>
		{else}
	    <td nowrap><strong>{$item.email}</strong></td>
	    {/if}
		{foreach name=demo from=$fields key=demo_id item=demo}
		    {if $parentEmailFieldID ne $demo_id and $passwordFieldID ne $demo_id}
                        {if $item.data.$demo_id|count_characters:true > 60} 
			        <td nowrap>
                                <div id='BigData_{$item.id}_{$demo_id}' style='display: inline;' onMouseOver="javascript:document.getElementById('BigData_{$item.id}_{$demo_id}_Full').style.display = 'inline';" onMouseOut="javascript:document.getElementById('BigData_{$item.id}_{$demo_id}_Full').style.display = 'none';">
                                <div id='BigData_{$item.id}_{$demo_id}_Full' class='BigDataHoverer'>
                                {if $demo.type == 'bigtext' && $item.bigdata.$demo_id != ""}{$item.bigdata.$demo_id|nl2br}{else}{$item.data.$demo_id}{/if}
                                </div>
                                {$item.data.$demo_id|truncate:60:"...":true}
                                </div>
                                </td>
                        {else}
			        <td nowrap>{$item.data.$demo_id}</td>
                        {/if}
            {/if}
		{/foreach}
		<td nowrap>{$item.date}</td>
	</tr>
	{/foreach}
	
	<tr>
		<td colspan="4">
			<b><a href="javascript:SetChecked(1,'sid[]')">{t}Check All{/t}</a> 
			&nbsp;&nbsp; || &nbsp;&nbsp; 
			<a href="javascript:SetChecked(0,'sid[]')">{t}Clear All{/t}</a></b>
		</td>
	</tr>

</table>

<SELECT name="action">
	<option value="" SELECTED>{t}Ignore{/t} {t}checked {$smarty.const.bm_SubscriberWord}s{/t}</option>
	<option value="delete">{t}Delete{/t} {t}checked {$smarty.const.bm_SubscriberWord}s{/t}</option>
	{if $table == 'subscribers'}
		<option value="edit">{t}Edit{/t} {t}checked {$smarty.const.bm_SubscriberWord}s{/t}</option>
	{else}
		<option value="add">{t}Add{/t} {t}checked {$smarty.const.bm_SubscriberWord}s{/t}</option>
	{/if}
</SELECT>

&nbsp;&nbsp;&nbsp; 
<input type="submit" name="send" value="{t}go{/t}">

</form>

<br><br>

{$pagelist}

</div>

</table>


{literal}
<script type="text/javascript">
// <![CDATA[

/* The following code is to "check all/check none" NOTE: form name must properly be set */
var form='oForm' //Give the form name here
function SetChecked(val,chkName) {
	dml=document.forms[form];
	len = dml.elements.length;
	var i=0;
	for( i=0 ; i<len ; i++) {
		if (dml.elements[i].name==chkName) {
			dml.elements[i].checked=val;
		}
	}
}
// ]]>
</script>
{/literal}

{include file="admin/inc.footer.tpl"}