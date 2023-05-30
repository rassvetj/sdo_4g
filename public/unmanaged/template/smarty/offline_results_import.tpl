<form action="" method="POST" enctype="multipart/form-data">
<input name="action" type="hidden" value="import">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Импортировать результаты{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Файл результатов{?/t?} (zip): </td>
    <td><input type="file" name="results" style="width: 300px;">
        {?$tooltip->display('result_import')?}
    </td>
</tr>
<tr>
    <td colspan=2>{?$OKBUTTON?}</td>
</tr>
</table>
</form>