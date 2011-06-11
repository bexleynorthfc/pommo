<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" xml:lang="en" lang="en">

<head>

	<meta http-equiv="Content-Type" content="text/html; charset=UTF-8" />
	<title>{$title}</title>
	
        <!-- Added by Trevor Mills to integrate his CMS -->
        <LINK REL=StyleSheet HREF='{$tmCSSDirectory}style.css' TYPE='text/css'>
        <style type="text/css"> 
        <!--
         /*IE and NN6x styles, this won't import in Netscape 4.x*/
           @import url('{$tmCSSDirectory}style_breaks_netscape.css');
        -->   
        .outlined_box{ldelim}
        font-size: 1.3em;
        {rdelim}
        </style>
        

	{if $loadCSS !== false}	
		<link href="{$url.theme.this}inc/admin.css" type="text/css" rel="STYLESHEET">
	{/if}
	
	
	{* If $head has been captured, print its contents here. Capture $head via templates
		using {capture name=head}..content..{/capture} before including this header file. 
		Useful for properly including javascripts and CSS in the HTML <head> *}
	{$smarty.capture.head}

	{* Include HTML FORM styling and javascript from shared theme directory when template
		is prepared to include a form from the parent PHP script *}    
	{if $isForm}
	<link href="{$url.theme.shared}css/bform.css" type="text/css" rel="STYLESHEET">
	<script type="text/javascript" src="{$url.theme.shared}js/bform.js"></script>
	{/if}

{* The following fixes transparent PNG issues in IE < 7 *}

	
</head>

<body {if $smarty.request.mode ne ''}class="mode-{$smarty.request.mode}"{/if}>

<center>

{include file="$tmSmartyDirectory/admin_nav.tpl"}

{if $header}
<div id="header">
	<h1>{$header.main}</h1>
	<h2>{$header.sub}</h2>
</div>
{/if}


<div id="content">

