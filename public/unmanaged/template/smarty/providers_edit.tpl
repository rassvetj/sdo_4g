<form action="" method="POST" enctype="multipart/form-data">
<input type="hidden" name="action" value="edit_post">
{?if $cert.id?}
<input type="hidden" name="id" value="{?$cert.id?}">
{?/if?}
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Поставщик электронных курсов{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?}:</td>
    <td><input style="width: 300px" type="text" name="title" value="{?$cert.title?}"></td>
</tr>
<tr>
    <td>{?t?}Адрес{?/t?}:</td>
    <td><textarea name="address" rows=10 cols=60>{?$cert.address?}</textarea></td>
</tr>
<tr>
    <td>{?t?}Контакты{?/t?}:</td>
    <td><textarea name="contacts" rows=10 cols=60>{?$cert.contacts?}</textarea></td>
</tr>
<tr>
    <td>{?t?}Описание{?/t?}:</td>
    <td><textarea name="description" rows=10 cols=60>{?$cert.description?}</textarea></td>
</tr>
</table>
{?$okbutton?}
</form>