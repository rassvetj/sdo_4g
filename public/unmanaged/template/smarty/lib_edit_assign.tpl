{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Библиотека учебных материалов'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<form method="POST">
<input type="hidden" name="update_assign" value="update_assign">
<input type="hidden" name="assid" value="{?$copy.assid?}">
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Редактировать карточку выдачи учебного материала{?/t?}</th></tr>
<tr><td>{?t?}Пользователь:{?/t?} </td><td>
{?include file="control_list.tpl"?}
</td></tr>
<tr>
<td>{?t?}Дата выдачи:{?/t?} </td>
<td>
{?html_select_date prefix="start" field_order="DMY" time=$copy.start?}
</td>
</tr>
<tr>
<td>{?t?}Необходимая дата возврата:{?/t?} </td>
<td>
{?html_select_date prefix="stop" field_order="DMY" end_year="+1" time=$copy.stop?}
</td>
</tr>
<tr>
<td>{?t?}Издания:{?/t?} </td>
<td>
    <input type="hidden" name="bids[]" value="{?$copy.bid?}">
    <b>{?$copy.title|escape?}</b><br>
</td>
</tr>
<tr>
<td>{?t?}Возвращен:{?/t?} </td>
<td>
<select name="closed">
<option value="0" {?if !$copy.closed?}selected{?/if?}> {?t?}Нет{?/t?}</option>
<option value="1" {?if $copy.closed?}selected{?/if?}> {?t?}Да{?/t?}</option>
</select>
</td>
</tr>
<tr><td colspan=2 align=right>
<input type="Submit" name="Submit" value="{?t?}Сохранить{?/t?}">
<input type="button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}lib.php?bid={?$copy.bid?}&action=history&page={?$page?}'">
</td></tr>
</table>
</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
