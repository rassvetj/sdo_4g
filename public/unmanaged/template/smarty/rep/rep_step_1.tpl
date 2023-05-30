{?php?}
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?assign var="first" value="true"?}

<form name="myform" action="{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=2" method="POST">
<input type="hidden" name="laststep" value="1">

<table width=100% class=main cellspacing=0>
{?foreach name=reps from=$reports item=rep?}
{?if in_array($rep.type,$rep_types)?}
{?if $rep.level ?}
    {?if !$rep.name?}
        {?if !$smarty.foreach.reps.first?}
            </td></tr>
        {?/if?}
        <tr><th class="intermediate">
            {?$rep.start_tag?}{?$rep.title?}{?$rep.end_tag?}
        </th></tr>
        <tr><td>
    {?else?}
            <p style="padding: 5px 0;">
            {?repeat string=' &nbsp;' count=$rep.level?}
            <input type="radio" {?if $first == "true"?}checked{?/if?} name="reportName" value="{?$rep.name?}">
            {?assign var="first" value="false"?}
            {?$rep.start_tag?}{?$rep.title?}{?$rep.end_tag?}
            </p>
    {?/if?}
{?/if?}
{?/if?}
{?/foreach?}
 </td></tr>
</table>
<br />
<div align=right>
{?$submit?}
</div>

</form>
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();
{?/php?}
