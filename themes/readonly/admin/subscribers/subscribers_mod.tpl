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

<div style="width:90%;">
	<span style="float: right; margin-right: 30px;">
		<a href="subscribers_manage.php">{t 1=$returnStr}Return to %1{/t}</a>
	</span>
</div>

<p style="clear: both;"></p>

	{foreach name=sub from=$subscribers key=key item=item}
	<hr>
		<div class="SubscriberData">
			<div class="Label">Email</div>
			<div class="Data">
				{if $parentEmailFieldID ne '' and $item.email eq 'none' and $item.data.$parentEmailFieldID ne '' and false}
				    {$item.data.$parentEmailFieldID}
				{else}
					<a href="mailto:{$item.email}">{$item.email}</a>
				{/if}
			</div>
		</div>
	
		{foreach name=demo from=$fields key=demo_id item=demo}
	        {if $passwordFieldID ne $demo_id}
		<div class="SubscriberData">
			<div class="Label">{$demo.name}</div>
			<div class="Data">
			{if $demo.type == 'text'}
				{$item.data.$demo_id}
			{elseif $demo.type == 'checkbox'}
				{if $item.data.$demo_id == 'on'}Yes{else}No{/if}
			{elseif $demo.type == 'multiple'}
				{$item.data.$demo_id}
	        {elseif $demo.type == 'multiplemultiple'}
				{$item.data.$demo_id}
	        {elseif $demo.type == 'bigtext'}
				{$item.data.$demo_id}
			{else}
  				{t}Unsupported Field Type.{/t}
  			{/if}
			</div>
		</div>
			{/if}
		{/foreach}
		<div style="clear:both">&nbsp;</div>
	{/foreach}

	
{include file="admin/inc.footer.tpl"}