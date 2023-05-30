{?include file="rep/rep_header.tpl"?}
<div class="rep-print">
<table width=100% cellspacing=0 cellpadding=4 class="print_report_table">
{?if $reportResults?}
    {?$reportResults?}
{?else?}
    <tr><td>{?t?}Данные не найдены{?/t?}</td></tr>
{?/if?}
</table>

<script language="JavaScript" type="text/javascript">
<!--
window.print();
// -->
</script>

{?include file="rep/rep_footer.tpl"?}
</div>
