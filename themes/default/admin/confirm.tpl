{if !$embeddedConfirm}
{include file="admin/inc.header.tpl"}
{include file="admin/inc.sidebar.tpl"}

<div id="mainbar">

{if $confirm.title}
	<h1>{$confirm.title}</h1>
{else}
	<h1>{t}Confirm{/t}</h1>
{/if}

<br>
{/if}
	<table border="0" cellspacing="0" cellpadding="0">
	<tr>
		<td colspan="2">{$confirm.msg}</td>
	</tr>
		
	<tr>
		<td nowrap>
			<img src="{$url.theme.shared}images/icons/alert.png" align="middle">{t}Confirm your action.{/t}
		</td>
		<td>
			<p>	
				<a href="{$confirm.yesurl|bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/ok.png" class="navimage">
				{t}Yes{/t}</a> {t}I confirm.{/t}
			</p>

			<p>
				<a href="{$confirm.nourl|bmAppendPoMMoInstance}">
				<img src="{$url.theme.shared}images/icons/undo.png" class="navimage" align="middle">
				{t}No{/t}</a> {t}Please Return.{/t}
			</p>
		  </td>
		</tr>
		</table>
{if !$embeddedConfirm}
</div>
<!-- end mainbar -->

{include file="admin/inc.footer.tpl"}
{/if}