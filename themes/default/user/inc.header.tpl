{capture name=head}
	{* Include HTML FORM styling and javascript from shared theme directory when template
		is prepared to include a form from the parent PHP script *}    
	{if $isForm}
	<link href="{$url.theme.shared}css/bform.css" type="text/css" rel="STYLESHEET">
	<script src="{$url.theme.shared}js/bform.js" type="text/javascript"></script>
	{/if}
{/capture}
{get_head}
