<form action="{?$sitepath?}groups.php" method="POST" onSubmit="select_list_select_all('list2');">
<input name="c" type="hidden" value="post_editgr">
<input name="gid" type="hidden" value="{?$group.gid?}">
<input name="cid" type="hidden" value="{?$cid?}">
<table width=100% class=main cellspacing=0>
    <!--tr>
        <th colspan="2">{?t?}Редактирование группы{?/t?}</th>
    </tr-->
    <tr>
        <td>{?t?}Название{?/t?}:</td>
        <td><input name="name" type="text" value="{?$group.name|escape?}" style="width: 300px;"></td>
    </tr>
    <tr><td colspan=2>{?$users?}</td></tr>
    <tr>
        <td colspan=2>
            <table align="right">
                <tr>
                    <td>{?$cancelbutton?}</td>
                    <td>{?$okbutton?}</td>
                </tr>
            </table>            
        </td>
    </tr>
</table>
</form>