{capture name=head}{* used to inject content into the HTML <head> *}
{if $action != 'delete'}
<script src="{$url.theme.shared}js/jquery.js" type="text/javascript"></script>
{literal}
<script language='javascript' type='text/javascript' src='{/literal}{$tmBaseURL}{literal}lib/js/tinymce/jscripts/tiny_mce/tiny_mce_src.js'></script>
<script language='javascript' type='text/javascript'>
    tinyMCE.init({
        theme : 'advanced',
    	mode : 'exact',
    	elements : 'body',
        theme_advanced_toolbar_location : 'top',
        plugins : 'fullscreen,media,table,smartycode,paste',
        theme_advanced_buttons1 : 'cut,copy,paste,pastetext,pasteword,undo,redo,|,bold,italic,underline,|,justifyleft,justifycenter,justifyright,justifyfull,|,'
        + 'styleselect,formatselect,fontselect,fontsizeselect,|,forecolor,backcolor',
        theme_advanced_buttons2 : 'bullist,numlist,outdent,indent,'
        + 'link,unlink,anchor,image,media,separator,'
        + 'removeformat,cleanup,code,smartycode,fullscreen,separator,charmap,|,tablecontrols',
        theme_advanced_buttons3 : '',
        width : '800px',
        file_browser_callback : 'myFileBrowser',
        relative_urls : false,
        tm_convert_urls : true,
        convert_urls : false, 
	    theme_advanced_resizing : true,
        theme_advanced_layout_manager : 'SimpleLayout',
        theme_advanced_statusbar_location : 'bottom',
        extended_valid_elements : ''
            +'object[align<bottom?left?middle?right?top|archive|border|class|classid'
              +'|codebase|codetype|data|declare|dir<ltr?rtl|height|hspace|id|lang|name'
              +'|onclick|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
              +'|onmouseout|onmouseover|onmouseup|standby|style|tabindex|title|type|usemap'
              +'|vspace|width],'
			+'a[accesskey|charset|class|coords|dir<ltr?rtl|href|hreflang|id|lang|name'
			  +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
			  +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|rel|rev'
			  +'|shape<circle?default?poly?rect|style|tabindex|title|target|type],'
			+'div[align<center?justify?left?right|class|dir<ltr?rtl|id|lang|onclick'
			  +'|ondblclick|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove'
			  +'|onmouseout|onmouseover|onmouseup|style|title],'
            +'param[id|name|type|value|valuetype<DATA?OBJECT?REF],'
            +'script[charset|defer|language|src|type],'
            +'form[accept|accept-charset|action|class|dir<ltr?rtl|enctype|id|lang'
              +'|method<get?post|name|onclick|ondblclick|onkeydown|onkeypress|onkeyup'
              +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onreset|onsubmit'
              +'|style|title|target],'
            +'style[dir<ltr?rtl|lang|media|title|type],'
            +'textarea[accesskey|class|cols|dir<ltr?rtl|disabled<disabled|id|lang|name'
              +'|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
              +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
              +'|readonly<readonly|rows|style|tabindex|title],'                
            +'input[accept|accesskey|align<bottom?left?middle?right?top|alt'
              +'|checked<checked|class|dir<ltr?rtl|disabled<disabled|id|ismap<ismap|lang'
              +'|maxlength|name|onblur|onclick|ondblclick|onfocus|onkeydown|onkeypress'
              +'|onkeyup|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|onselect'
              +'|readonly<readonly|size|src|style|tabindex|title'
              +'|type<button?checkbox?file?hidden?image?password?radio?reset?submit?text'
              +'|usemap|value],'
            +'option[class|dir<ltr?rtl|disabled<disabled|id|label|lang|onclick|ondblclick'
              +'|onkeydown|onkeypress|onkeyup|onmousedown|onmousemove|onmouseout'
              +'|onmouseover|onmouseup|selected<selected|style|title|value],'
            +'select[class|dir<ltr?rtl|disabled<disabled|id|lang|multiple<multiple|name'
              +'|onblur|onchange|onclick|ondblclick|onfocus|onkeydown|onkeypress|onkeyup'
              +'|onmousedown|onmousemove|onmouseout|onmouseover|onmouseup|size|style'
              +'|tabindex|title],'  
            +'embed[src|flashVars|menu|quality|wmode|bgcolor|width|height|type|pluginspage]',
        force_p_newlines : true,
	    apply_source_formatting : true
    });

function myFileBrowser (field_name, url, type, win) {
    var cmsURL = '{/literal}{$tmAdminBaseURL}{$bootstrap->makeAdminURL('Common','file_browser')}{literal}';      // script URL

    // newer writing style of the TinyMCE developers for tinyMCE.openWindow

	tinyMCE.activeEditor.windowManager.open({
        file : cmsURL + '&type=' + type, // PHP session ID is now included if there is one at all
        title : '{/literal}{$bootstrap->getAdminTitle('Common','file_browser')}{literal}',
        width : 800,  // Your dimensions may differ - toy around with them!
        height : 600,
        close_previous : 'no',
        resizable : 'yes',
        scrollbars : 'yes'
	},
	{
        window : win,
        input : field_name,
        browser_type : type
	});
	
	return false;
}

</script>	
{/literal} 
{/if}
{/capture}
{include file="admin/inc.header.tpl"}
{assign var='templateCount' value=$templates|@count}
</div>

<h1>{$actionStr}</h1>
{if $messages}
	<div class="msgdisplay">
		{foreach from=$messages item=msg}
			<div>* {$msg}</div>
		{/foreach}
	</div>
{/if}
{if $errors}
	<br>
	<div class="errdisplay">
		{foreach from=$errors item=msg}
			<div>* {$msg}</div>
		{/foreach}
	</div>
{/if}

<div style="width:100%;">
	<span style="float: right; margin-right: 30px;">
		<a href="mailings_templates.php{"?"|bmAppendPoMMoInstance}">{t 1=$returnStr}Return to %1{/t}</a>
	</span>
</div>
<p style="clear: both; text-align:center;"><hr></p>

<form name="aForm" id="aForm" method="POST" action="">
	<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">

<p><span style="text-align: center;">
{if $action == 'edit'}
	{t 1=$templateCount}Editing %1 templates.{/t}
{elseif $action == 'delete'}
	{t 1=$templateCount}The following %1 templates will be deleted{/t}:
	<p>
		<input type="submit" name="deleteTemplates" value="{t}Delete Templates{/t}">
	</p>
{/if}
</span></p>

		
{foreach from=$templates key=key item=template}
	{if $action == 'delete'}
	<input type="hidden" name="delid[]" value="{$template.id}">
	{else}
	<input type="hidden" name="templateid" value="{$template.id}">	
	{/if}
	<input type="hidden" name="action" value="{$action}">	
	<div style="width: 80%; text-align:left;margin:0px auto">
		<table border="0" cellpadding="0" cellspacing="0" style="text-align:left; padding:10px;">
			<tr>
				<td>
					{if $action == 'delete'}
						<p><b>{t}Name:{/t} </b>{$template.name}</p>
						<fieldset>
							<legend>{t}Body{/t}</legend>
							<div>{$template.body}</div>
						</fieldset>
					{else}
						<p><b>{t}Name:{/t} </b><input type='text' name='name' value='{$template.name|escape}'></p>
					<fieldset>
						<legend>{t}Body{/t}</legend>
						<textarea id="body" name="body" rows="10" cols="80" style="width: 100%;">{$template.body}</textarea>
					</fieldset>

					<br>
					<div style="position: relative; width: 100%; z-index: 1; text-align: right">
						<a class="pommoOpen" href="#">{t}Add Personalization{/t}</a>
							<div id="selectField" style="z-index: 2; text-align: left; display: none; position: absolute; top: -5px; right: -5px; width: 90%; background-color: #e6eaff; padding: 7px; border: 1px solid;">
								<div class="pommoHelp">
									<img src="{$url.theme.shared}images/icons/help.png" align="absmiddle" border="0" style="float: right; margin-left: 10px;">
									<span style="font-weight: bold;">{t}Add Personalization{/t}: </span>
									<span class="pommoHelp">
									{t}Mailings can be personalized by adding subscriber field values to the body. For instance, you can have mailings begin with "Dear Susan, ..." instead of "Dear Subsriber, ...". The syntax for personalization is; [[field_name]] or [[field_name|default_value]]. If 'default_value' is supplied and a subscriber has no value for 'field_name', [[field_name|default_value]] will be replaced by default_value. If no default is supplied and no value exists for the field, [[...]] will be replaced with a empty (blank) string, allowing mailings to start with "Dear [[firstName|Friend]] [[lastName]]," (example assumes firstName and lastName are collected fields).{/t}
									</span>
									<hr style="clear: both;">
								</div>

								<select id="field">
									<option value="">{t}choose field{/t}</option>
									<option value="Email">Email</option>
									{foreach from=$fields key=id item=field}
									<option value="{$field.name}">{$field.name}</option>
									{/foreach}
								</select>

								<span style="margin-left: 20px; margin-right: 20px;"> Default: <input type="text" id="default"></span>

								<input id="insert" type="button" value="{t}Insert{/t}">

								<br>

								<a class="pommoClose" href="#" style="float: right;">
										<img src="{$url.theme.shared}images/icons/left.png" align="absmiddle" border="0">{t}Go Back{/t}
								</a>				
							</div>
					</div>
					{/if}
				</td>	
			</tr>
		</table>
	</div>
	
	<br>
{/foreach}

{if $action == 'delete'}
<p>
	<input type="submit" name="deleteTemplates" value="{t}Delete Templates{/t}">
</p>
{else}
<p>
	<input type="submit" name="saveTemplate" value="{t}Save Template{/t}">
</p>
{/if}
			
</form>

{literal}
<script type="text/javascript">

$(function() {

	
	/********
	
	$("#personalize").click(function() {
		$("#selectField").slideDown('slow', function() {
			$(this).find("a.pommoClose").click(function() {
					$("#selectField").slideUp('slow', function() { $(this).unclick(); });
					return false;
				});
			});
		return false;
		});
	***********/
	
	$("a.pommoOpen").click(function() { $(this).siblings("div").slideDown(); return false; });
		
	$("a.pommoClose").click(function() { $(this).parent().slideUp(); return false; });
	
	$("div.pommoHelp img").click(function() {
		$(this).parent().find("span.pommoHelp").toggle(); return false;
		});
		
	$("#insert").click(function() {		
		if ($("#field").val() == '') { 
			alert ('{/literal}{t}You must choose a field{/t}{literal}'); 
			return false; 
			}
		
		// sting to append
		var str = '[['+($("#field").val())+(($("#default").val() == '')? '' : '|'+$("#default").val())+']]';
		
		// append to plain text editor (regular textarea)
		tinyMCE.execCommand('mceInsertContent', false, str);
		
		// hide dialog
		$("#field").add("#default").val("");
		$(this).parent().hide();
		
		return false;
	});
});
</script>
{/literal}

{include file="admin/inc.footer.tpl"}

