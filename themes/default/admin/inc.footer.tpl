
</div>
<!-- end content -->

<p class="clearer"></p>
<div id="footer">
&nbsp;<br />
 {t escape="no" url='<a href="http://www.pommo.org/">poMMo</a>'}Page fueled by %1 mailing management software.{/t}
</div>
<!-- end footer -->

</center>

{* If $foot has been captured, print its contents here. Capture $foot via templates
	using {capture name=foot}..content..{/capture} before including this footer file. 
	Useful for properly including javascripts and CSS in the HTML *}
{$smarty.capture.foot}
</body>
</html>