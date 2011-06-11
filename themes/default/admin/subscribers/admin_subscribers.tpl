{include file="admin/inc.header.tpl"}
{include file="admin/inc.sidebar.tpl"}

<div id="mainbar">

	<h1>{t}{$smarty.const.bm_SubscriberWord}s Page{/t}</h1>
			
	<p>
		<a href="{$url.base}admin/subscribers/subscribers_manage.php{"?"|bmAppendPoMMoInstance}">
		<img src="{$url.theme.shared}images/icons/examine.png" class="navimage" />
		{t}Manage{/t}</a> -
		{t}{$smarty.const.bm_SubscriberWord}s. See an overview of your current and pending {$smarty.const.bm_SubscriberWord}s. You can add, delete, and edit {$smarty.const.bm_SubscriberWord}s from here.{/t}
		<br>&nbsp;
	</p>		
						
	{if $poMMo_package|is_a:'Package' and $poMMo_package->add_menu_items|is_array}
		{foreach from=$poMMo_package->add_menu_items key=key item=menu_item_parms}
		<p>
			<a href="{$url.base}admin/subscribers/{$menu_item_parms.file|bmAppendPoMMoInstance}">
			<img src="{$url.theme.shared}images/icons/{$menu_item_parms.icon}" class="navimage" />
			{t}{$key}{/t}</a> - 
			{t}{$menu_item_parms.description}{/t}
			<br>&nbsp;
		</p>	
		{/foreach}
	{/if}

	<p>
		<a href="{$smarty.const.CMS_ADMIN_URL}{$bootstrap->getAdminURL()}&amp;package=ImportExport&amp;{$smarty.const.ADMIN_PAGE_PARM}=import&amp;p={$poMMo_package->package_name}" target="_top">
		<img src="{$url.theme.shared}images/icons/import.png" class="navimage" />							
		{t}Import{/t}</a> - 
		{t}{$smarty.const.bm_SubscriberWord}s.  You can import large amounts of {$smarty.const.bm_SubscriberWord}s using files stored on your computer.{/t}
		<br>&nbsp;
	</p>

	<p>
		<a href="{$url.base}admin/subscribers/subscribers_groups.php{"?"|bmAppendPoMMoInstance}">
		<img src="{$url.theme.shared}images/icons/groups.png" class="navimage" />
		{t}Groups{/t}</a> - 
		{t}Manage "mailing groups" from this area. Mailing groups allow you to mail subsets of your {$smarty.const.bm_SubscriberWord}s, rather than just the entire list.{/t}
		<br>&nbsp;
	</p>
	
	{apply_filters filter="pommo_admin_subscribers_items"}
 
</div>
<!-- end mainbar -->

{include file="admin/inc.footer.tpl"}