{?php?}
echo show_tb();
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_("Отчет")." &laquo;" . $GLOBALS['reports'][$GLOBALS['s']['reports']['current']['name']]['title'] . "&raquo;");
{?/php?}
<link rel="stylesheet" href="{?$sitepath?}styles/report.css" type="text/css">
<script type="text/javascript">
<!--
function wopen(url,name,x,y) {
	if (x==undefined) x=790;
	if (y==undefined) y=575;
	if (name==undefined) name="name"+x+y;
	window.open(url,name,"toolbar=0,location=0,directories=0,status=1,menubar=0,"+
	"scrollbars=1,resizable=1,width="+x+",height="+y);
}


    function do_filters(element) {

        if (element && element.checked) show_filters(element);
        else hide_filters(element);

    }

    function show_filters(element) {

        var method = (document.all) ? 'block' : 'table-row';

        var elm = document.getElementById('filters_rep');
        if (elm) elm.style.display = method;

    }

    function hide_filters(element) {

        var method = 'none';

        if (confirm('{?t?}Убрать фильтр?{?/t?}')) {

        i=1;

        var item = document.getElementById('filter_'+i);

        while (item) {

            item.value = '';

            i++;
            item = document.getElementById('filter_'+i);

        }

        var elm = document.getElementById('filters_rep');
        if (elm) elm.style.display = method;

        document.getElementById('myForm').submit();

        } else {

            if (element) element.checked = true;

        }

    }


// -->
</script>

{?*include file="rep/rep_header.tpl"*?}

<form id="myForm" action="{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=3" method="POST">
<input type="hidden" name="laststep" value="3">
{?if !$report->disable_filter?}
<table width=100% border=0 cellspacing=0 cellpadding=2>
<tr>
    <td>
    <input type="checkbox" id="do_filter" name="do_filter" onClick="do_filters(this);"> {?t?}фильтр{?/t?} &nbsp;
    <input type="Submit" name="Submit" value="{?t?}Применить{?/t?}">
    </td>
</tr>
</table><br>
{?/if?}
<div class="unmanaged-report-step3">
<table class=main cellspacing=0>
{?if $report->data.data?}
    {?$reportResults?}
{?else?}
    <tr><td class="nodata">{?t?}Нет данных для отображения{?/t?}</td></tr>
{?/if?}
</table>
</div>
</form>

{?if $plots?}
<table border=0 align=center>
{?foreach from=$plots item=plot?}
  <tr>
    <td><img src="{?$plot.url?}" border=0></td>
  </tr>
{?/foreach?}
</table>
{?/if?}
<br>
<!--div align=right>
<input type="button" name="cancel" onClick="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step={?$prev?}'" value="<< {?t?}Назад{?/t?}">
<input type="button" name="cancel" onClick="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=1'" value="{?t?}Главная{?/t?}">
<input onClick="wopen('{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=4')" type="button" name="print" value="{?t?}Печать{?/t?}">
<input type="button" onClick="document.location.href='{?$sitepath?}rep.php?type={?$s.reports.current.type?}&step=5'" name="save" value="{?t?}Экспорт в Excel{?/t?}"></div-->
<table align='right'>
    <tr>
        <td>{?$cancel?}</td>
        <td>{?$main?}</td>
        <td>{?$print?}</td>
        <td>{?$save?}</td>
    </tr>
</table>

{?include file="rep/rep_footer.tpl"?}

<script type="text/javascript">
<!--
        if (document.getElementById('filters_rep') && !document.getElementById('filters_rep').style.display)
        document.getElementById('do_filter').checked = true;
// -->
</script>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
echo show_tb();
{?/php?}
