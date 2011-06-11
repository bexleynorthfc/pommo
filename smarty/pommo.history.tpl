{*Smarty*}
{if $TotalPages > 1}
<p><strong>Page: </strong>
    {section name=pages loop=$TotalPages+1 start=1}
        {if $smarty.section.pages.index eq $smarty.get.page or ($smarty.get.page eq '' and $smarty.section.pages.index eq 1)}
            {$smarty.section.pages.index}
        {else}
            <a href='{$SelfURL}?page={$smarty.section.pages.index}'>{$smarty.section.pages.index}</a>
        {/if}
        
    {/section}
</p>
{/if}<table cellpadding="5" id="pommo-history-table">
    <tr>
        <th>Date</th>
        <th>Subject</th>
    </tr>
        
{foreach name=mailloop from=$Mailings key=key item=mailitem}
    {assign var=timestamp value=$mailitem.started|strtotime}
    <tr>
        <td>{"F d, Y"|date:$timestamp}</td>
        <td><a href='{$SelfURL}?mailing={$mailitem.mailid}'>{$mailitem.subject}</a></td>
    </tr>
{/foreach}
</table>
{if $TotalPages > 1}
<p><strong>Page: </strong>
    {section name=pages loop=$TotalPages+1 start=1}
        {if $smarty.section.pages.index eq $smarty.get.page or ($smarty.get.page eq '' and $smarty.section.pages.index eq 1)}
            {$smarty.section.pages.index}
        {else}
            <a href='{$SelfURL}?page={$smarty.section.pages.index}'>{$smarty.section.pages.index}</a>
        {/if}
        
    {/section}
</p>
{/if}