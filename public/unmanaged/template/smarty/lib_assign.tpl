{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<form method="POST">
<input type="hidden" name="assign" value="assign">
<input type="hidden" name="type" value="1">
<input type="hidden" name="mid" value="{?$smarty.session.s.mid?}">
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Формуляр издания{?/t?}</th></tr>
<tr>
<td>{?t?}Дата резервирования:{?/t?} </td>
<td>
{?html_select_date prefix="start" field_order="DMY"?}
</td>
</tr>
<tr>
<td>{?t?}Зарезервировать до:{?/t?} </td>
<td>
{?html_select_date prefix="stop" field_order="DMY" end_year="+1"?}
</td>
</tr>
<tr>
<td>{?t?}Кол-во экземпляров{?/t?}:</td>
<td>
<select name="number">
{?section name="number" loop=$books.0.copies+1 start=1?}
<option value="{?$smarty.section.number.index?}"> {?$smarty.section.number.index?}</option>
{?/section?}
</select>
</td>
</tr>
<tr>
<td>{?t?}Издание:{?/t?} </td>
<td>
{?foreach from=$books item=i?}    
    <input type="hidden" name="bids[]" value="{?$i.bid?}">
    <b>{?$i.title|escape?}</b><br>
{?/foreach?}
</td>
</tr>
<tr><td colspan=2 align=right>
<input type="Submit" name="Submit" value="{?t?}Зарезервировать{?/t?}">
<input type="button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}lib.php?page={?$page?}'">
</td></tr>
</table>
</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
