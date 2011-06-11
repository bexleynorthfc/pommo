{include file="admin/inc.header.tpl"}

</div>
<!-- begin content -->

	<h1>{t}Mailings Templates{/t}</h1>

		{* Display a eventual error message *}
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
				<a href="admin_mailings.php{"?"|bmAppendPoMMoInstance}">{t 1=$returnStr}Return to %1{/t}</a>
			</span>
		</div>
		<p style="clear: both;"></p>
		<hr>


    	<!-- Ordering options -->
		<div style="text-align: center; width: 100%;" >
	
			<form name="bForm" id="bForm" method="POST" action="">
				<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
		
				{t}Templates per Page:{/t} 
			
				<SELECT name="limit" onChange="document.bForm.submit()">
					<option value="10"{if $state.limit == '10'} SELECTED{/if}>10</option>
					<option value="20"{if $state.limit == '20'} SELECTED{/if}>20</option>
					<option value="50"{if $state.limit == '50'} SELECTED{/if}>50</option>
					<option value="100"{if $state.limit == '100'} SELECTED{/if}>100</option>
				</SELECT>
		
				<span style="width: 30px;"></span>
	
				{t}Order by:{/t}
				<SELECT name="sortBy" onChange="document.bForm.submit()">
					<option value="name"{if $state.sortBy == 'name'} SELECTED{/if}>Name</option>
				</SELECT>

				<span style="width: 15px;"></span>
	
				<SELECT name="sortOrder" onChange="document.bForm.submit()">
					<option value="ASC"{if $state.sortOrder == 'ASC'} SELECTED{/if}>{t}ascending{/t}</option>
					<option value="DESC"{if $state.sortOrder == 'DESC'} SELECTED{/if}>{t}descending{/t}</option>
				</SELECT>
	
			</form>
			
		</div>
		<!-- End Ordering Options -->

		<br><br>
		<div style="text-align: center; width: 100%;" >
			( <em>{t 1=$rowsinset}%1 templates{/t}</em> )
		</div>

		<!-- Table of Templates -->
		<div style="text-align: center; width: 100%;" id="mailingtable" >
	
		<form name="oForm" id="oForm" method="POST" action="mailings_templates_mod.php">
			<input type="hidden" name="pk" value="{$smarty.const.poMMo_instance}">
			<table cellspacing="0" cellpadding="5" border="0" style="text-align: left; margin: 10px; margin-left:auto; margin-right:auto; ">

					<!--Table headers-->

					<tr>
							<td nowrap style="text-align:center;">{t}select{/t}</td>
							<td nowrap style="text-align:center;">{t}delete{/t}</td>
							<td nowrap style="text-align:center;">{t}edit{/t}</td>
					  		<td nowrap style="text-align:center;"><b>{t}Name{/t}</b></td>
					</tr>

			
					<!-- The Templates -->	
				{foreach name=templateloop from=$templates key=key item=templateitem}
					<tr bgcolor="{cycle values="#EFEFEF,#FFFFFF"}">

							<td style="text-align:center;" nowrap>
									<input type="checkbox" name="templateid[]" value="{$templateitem.templateid}">
							</td>
						
							<td style="text-align:center;" nowrap>
									<a href="mailings_templates_mod.php?templateid={$templateitem.templateid}&action=delete{"&"|bmAppendPoMMoInstance}">{t}delete{/t}</a>
							</td>

							<td style="text-align:center;" nowrap>
								<a href="mailings_templates_mod.php?templateid={$templateitem.templateid}&action=edit{"&"|bmAppendPoMMoInstance}">{t}edit{/t}</a>
							</td>

							<td nowrap><i>{$templateitem.name}</i></td>
					</tr>				
				{foreachelse}
					<tr>
						<td colspan="11">
							{t}No templates found.{/t}
						</td>
					</tr>
				
				{/foreach}
				
				

					<tr>
							<td colspan="12" style="text-align:left;">
								<b><a href="javascript:SetChecked(1,'templateid[]');">{t}Check All{/t}</a> 
								&nbsp;&nbsp; || &nbsp;&nbsp; 
								<a href="javascript:SetChecked(0,'templateid[]');">{t}Clear All{/t}</a></b>
							</td>
					</tr>
				
			</table>
		</div>

		<div style="text-align: center; width: 100%;" >
		
			<SELECT name="action">
					<option value="create">{t}Create{/t} {t}new template{/t}</option>
					<option value="delete">{t}Delete{/t} {t}checked templates{/t}</option>
			</SELECT>

			&nbsp;&nbsp;&nbsp; 
			<input type="submit" name="send" value="{t}go{/t}">
					
			<br><br>
			{$pagelist}

		</form>
	
		</div>

		<!-- End Table of Templates -->



	<!-- end mainbar -->

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