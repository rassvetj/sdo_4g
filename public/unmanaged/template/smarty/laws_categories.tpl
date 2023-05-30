<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="post_action" value="import">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Импортировать разделы{?/t?}</th></tr>
<tr>
    <td>{?t?}Файл разделов{?/t?} (.xml): </td>
    <td>
    <input style="width: 300px;" type="file" name="rubrics">
    <input type="Submit" name="Submit" value="{?t?}Импортировать{?/t?}">
    <input type="Button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}laws.php?page={?$page?}&sort={?$sort?}'">
    </td>
</tr>
</table>
<p>                                                       
</form>
