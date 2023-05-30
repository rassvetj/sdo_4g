<form action="{?$sitepath?}addgroup.php4" method="POST" onSubmit="select_list_select_all('list2');">
<input name="c" type="hidden" value="edit_post">
<input name="gid" type="hidden" value="{?$group.gid?}">
<table width=100% class=main cellspacing=0>
    <tr>
        <td>{?t?}Название{?/t?}</td>
        <td><input name="name" type="text" value="{?$group.name|escape?}" style="width: 300px;"></td>
    </tr>
    <tr><td colspan=2>{?$users?}</td></tr>
</table>
<table border="0" cellspacing="0" width="100%">
      <tr>
        <td align="right" width="99%">
            {?$okbutton?}
        </td>
        <td align="right" width="1%">
        <div style='float: right;' class='button'><a href='{?$sitepath?}addgroup.php4'>{?t?}Отмена{?/t?}</a></div><input type='button' value='{?t?}отмена{?/t?}' style='display: none;'/><div class='clear-both'></div>
        </td>
      </tr>
</table>
</form>