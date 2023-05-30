{?include file="all_header.tpl"?}

{?php?}
echo ph(_('История пользования материалом'));
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

<input type="hidden" name="drop_copy" value="drop_copy">
<input type="hidden" name="bid" value="{?$book.bid?}">
<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Информация о издании{?/t?}</th></tr>
<tr>
<td>{?t?}Издание:{?/t?} </td>
<td>{?$book.title?}</td>
</tr>
<tr>
<td>{?t?}Инвентарный номер:{?/t?} </td>
<td>{?$book.uid?}</td>
</tr>
<tr>
<td>{?t?}Количество экземпляров:{?/t?} </td>
<td>{?$book.quantity?}</td>
</tr>
<tr>
<td>{?t?}Количество свободных экземпляров:{?/t?} </td>
<td>{?$book.copies?}</td>
</tr>
</table>
<br>

<table width=100% class=main cellspacing=0>
<tr><th>{?t?}Выдать издание пользователю{?/t?}</th></tr>
<tr><td>
<form action="{?$sitepath?}lib.php?action=pre_assign&page={?$page?}" method="POST">
<input type="hidden" name="bids[]" value="{?$book.bid?}">
{?include file="control_list.tpl"?}
<br>
<input type="Submit" name="Submit" value="{?t?}Выдать{?/t?}"><br><br>
</form>
</td>
</tr>
</table><br>
<table width=100% class=main cellspacing=0>
<tr><th>{?t?}Дата выдачи{?/t?}</th><th>{?t?}Дата сдачи{?/t?}</th><th>{?t?}Пользователь{?/t?}</th><th>{?t?}Действие{?/t?}</th></tr>
{?if $history?}
{?foreach from=$history item=i?}
<tr><td>{?$i.start|date_format:"%d.%m.%Y"?}</td><td>{?$i.stop|date_format:"%d.%m.%Y"?}</td><td>{?$i.fio?}</td>
<td>
<a href="{?$sitepath?}lib.php?bid={?$book.bid?}&assid={?$i.assid?}&action=edit_assign&page={?$page?}">
<img alt="{?t?}Редактировать{?/t?}" border=0 src="images/icons/edit.gif"></a>
{?if !$i.closed?}
<a onClick="if (confirm('{?t?}Отметить сдачу учебного издания?{?/t?}')) return true; else return false;" href="{?$sitepath?}lib.php?bid={?$i.bid?}&assid={?$i.assid?}&action=history&page={?$page?}"><img alt="{?t?}Отметить сдачу издания?{?/t?}" border=0 height=15 src="images/icons/ico_work_.gif"></a>
{?/if?}
</td></tr>
{?/foreach?}
{?/if?}
</table>

<p align=right><input type="button" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}lib.php?page={?$page?}'">

<p>&nbsp;</p>
{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_footer.tpl"?}
