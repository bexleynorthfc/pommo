
<div id="sidebar">
	{if $section == "setup"}
	<!-- start section nav -->
	<h1>{t}Setup{/t}</h1>
	<div class="submenu">
		<a href="setup_configure.php{"?"|bmAppendPoMMoInstance}">{t}Configure{/t}</a> 
		<a href="setup_fields.php{"?"|bmAppendPoMMoInstance}">{t}Fields{/t}</a>
		<a href="setup_form.php{"?"|bmAppendPoMMoInstance}">{t}Setup Form{/t}</a>
	</div>
	<!-- end section nav -->
	{elseif $section == "mailings"}
	<!-- start section nav -->
	<h1>{t}Setup{/t}</h1>
	<div class="submenu">
		<a href="mailings_send.php{"?"|bmAppendPoMMoInstance}">{t}Send{/t}</a> 
		<a href="mailings_templates.php{"?"|bmAppendPoMMoInstance}">{t}Templates{/t}</a> 
		<a href="mailings_history.php{"?"|bmAppendPoMMoInstance}">{t}History{/t}</a>
	</div>
	<!-- end section nav -->
	{elseif $section == "subscribers"}
	<!-- start section nav -->
	<h1>{t}Setup{/t}</h1>
	<div class="submenu">
		<a href="subscribers_manage.php{"?"|bmAppendPoMMoInstance}">{t}Manage{/t}</a> 
		{if $poMMo_package|is_a:'Package' and $poMMo_package->add_menu_items|is_array}
			{foreach from=$poMMo_package->add_menu_items key=key item=menu_item_parms}
				<a href="{$menu_item_parms.file|bmAppendPoMMoInstance}">{t}{$key}{/t}</a>
			{/foreach}
		{/if}
		<a href="{$smarty.const.CMS_ADMIN_URL}{$bootstrap->getAdminURL()}&amp;package=ImportExport&amp;{$smarty.const.ADMIN_PAGE_PARM}=import&amp;p={$poMMo_package->package_name}" target="_top">{t}Import{/t}</a> 
		<a href="subscribers_groups.php{"?"|bmAppendPoMMoInstance}">{t}Groups{/t}</a>
	</div>
	<!-- end section nav -->
	{/if}

	<!-- begin nav -->
	<h1>Sections</h1>
	<div class="submenu">
		<a href="{$url.base}admin/mailings/admin_mailings.php{"?"|bmAppendPoMMoInstance}">{t}Mailings{/t}</a>
		<a href="{$url.base}admin/subscribers/admin_subscribers.php{"?"|bmAppendPoMMoInstance}">{t}{$smarty.const.bm_SubscriberWord}s{/t}</a>
		<a href="{$url.base}admin/setup/admin_setup.php{"?"|bmAppendPoMMoInstance}">{t}Setup{/t}</a>	
	</div>
	<!-- end nav -->
	{if $config.demo_mode == "on"}
	<p><img src="{$url.theme.shared}images/icons/demo.png" class="sideimage">{t}Demonstration mode is ON.{/t}</p>	
	{else}
	<p><img src="{$url.theme.shared}images/icons/nodemo.png" class="sideimage">{t}Demonstration mode is OFF.{/t}</p>	
	{/if}

</div>
<!-- end sidebar -->