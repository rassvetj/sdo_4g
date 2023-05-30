<form action="" method="POST" enctype="multipart/form-data">
<input name="action" type="hidden" value="import">
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Результаты импортирования{?/t?}</th>
</tr>
{?if $results?}
    {?foreach from=$results key=k item=v?}
    <tr><td><a onclick="window.open('redved.php4?SHEID={?$k?}', '_', 'toolbar=0, status=0, menubar=0, scrollbars=1, resizable=1, width=600');return false" href="redved.php4?SHEID={?$k?}">{?$v?}</a></td></tr>
    {?/foreach?}
{?else?}
<tr><td align=center>{?t?}не найдено новых результатов{?/t?}</td></tr>
{?/if?}
</table>
</form>