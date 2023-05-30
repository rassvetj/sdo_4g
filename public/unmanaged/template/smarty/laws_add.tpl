<form method="POST" enctype="multipart/form-data">

<input type="hidden" name="post_action" value="add">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Добавить новый документ{?/t?}</th></tr>
<tr>
    <td>{?t?}Название документа:{?/t?} <font color=red>*</font></td>
    <td><input style="width: 300px" type="text" name="title" value=""></td>
</tr>
<tr>
    <td>{?t?}Автор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="author" value=""></td>
</tr>
<tr>
    <td>{?t?}Инициатор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="initiator" value=""></td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?}: </td>
    <td><textarea name="annotation" style="width: 300px;"></textarea></td>
</tr>
<tr>
    <td>{?t?}Тип:{?/t?} </td>
    <td>
    <select name="type">
    {?html_options options=$types?}
    </select>
    </td>
</tr>
<tr>
    <td>{?t?}Регион применения:{?/t?} </td>
    <td>
    <select name="region">
    {?html_options options=$regions?}
    </select>
    </td>
</tr>
<tr>
    <td>{?t?}Область применения:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="area_of_application" value=""></td>
</tr>
<tr>
    <td>{?t?}Дата создания:{?/t?} </td>
    <td>
    {?html_select_date field_array="create_date" field_order="DMY" start_year="-100"?}
    </td>
</tr>
<tr>
    <td>{?t?}Срок действия:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="expire" value=""></td>
</tr>
<tr>
    <td>{?t?}Дата изменения:{?/t?} </td>
    <td>
    {?html_select_date field_array="modify_date" field_order="DMY" start_year="-100"?}
    </td>
</tr>
<tr>
    <td>{?t?}Причина редактирования:{?/t?} </td>
    <td><textarea name="edit_reason" style="width: 300px;"></textarea></td>
</tr>
<tr>
    <td>{?t?}Уровень доступа:{?/t?} </td>
    <td>
    <select name="access_level">
        {?php?}
        for($i=1;$i<=10;$i++) {

            echo "<option value=\"$i\"";
            //if ($i==10) echo " selected ";
            echo "> $i";

        }
        echo "<option value=\"0\" selected> 0";
        {?/php?}
    </select>
    </td>
</tr>
<tr>
    <td>{?t?}Материал:{?/t?} </td>
    <td>
    <input style="width: 300px" type="file" name="material" value="">
    </td>
</tr>
<tr>
    <td>{?t?}Текстовой вариант{?/t?} (.txt): </td>
    <td>
    <input style="width: 300px" type="file" name="index" value="">
    </td>
</tr>
<tr>
    <td colspan=2>{?t?}Категории{?/t?}</td>
<tr>
    <td colspan=2>
    <table width=100% border=0>
    <tr><td width=50%>
    <select size=5 id="cats" name="cats[]" multiple style="width: 100%">    
    {?if $categories?}
        {?foreach from=$categories item=i?}
            <option value="{?$i.catid?}"> {?$i.name?}</option>
        {?/foreach?}
    {?/if?}
    </select>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move('cats','categories','select_list_cmp_by_value')">
        <input type="button" value="<<" onClick="select_list_move('categories','cats','select_list_cmp_by_value')">
    </td>
    <td width=50%>
    <select size=5 id="categories" name="categories[]" multiple style="width: 100%">    
    </select>
    </td></tr>
    </table>
    </td>
</tr>
</table>
<p align=right><input type="Submit" name="Submit" value="{?t?}Добавить{?/t?}" onClick="select_list_select_all('categories');">
<input type="button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}laws.php?page={?$page?}&sort={?$sort?}'">
<p>
<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
</form>
