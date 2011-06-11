{include file="admin/inc.header.tpl"}
{include file="admin/inc.sidebar.tpl"}

<div id="mainbar">

	<h1>{t}{$smarty.const.bm_SubscriberWord}s Page{/t}</h1>
			
	<p>
		<a href="{$url.base}admin/subscribers/subscribers_manage.php{"?"|bmAppendPoMMoInstance}">
		<img src="{$url.theme.shared}images/icons/examine.png" class="navimage" />
		{t}Review{/t}</a> -
		{t}{$smarty.const.bm_SubscriberWord}s. See an overview of your current and pending {$smarty.const.bm_SubscriberWord}s. {/t}
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
 
</div>
<!-- end mainbar -->

{include file="admin/inc.footer.tpl"}