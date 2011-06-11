{*Smarty*}
{assign var=timestamp value=$Mailing.started|strtotime}
<div style='float:left;width:30%;text-align:left;padding:10px;'>
    <a href='{$SelfURL}'>Return to Index</a>
</div>
<div style='float:right;width:30%;text-align:right;padding:10px;'>
    {if $PreviousMailingID}<a href='{$SelfURL}?mailing={$PreviousMailingID}'>Previous</a>{/if}
    {if $NextMailingID}<a href='{$SelfURL}?mailing={$NextMailingID}'>Next</a>{/if}
</div>
<div style='background:white;padding:10px;clear:both'>
<p><strong>From: </strong>{$Mailing.fromname}</p>
<p><strong>To: </strong>{$Mailing.mailgroup}</p>
<p><strong>Date: </strong>{"F d, Y"|date:$timestamp}</p>
<p><strong>Subject: </strong>{$Mailing.subject}</p>
<div align="center">{$Mailing.body}</div>
<div style='clear:both'>&nbsp;</div>
</div>
