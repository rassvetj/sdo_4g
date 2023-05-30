<!--form id="editform" name="editform" method="POST" enctype="multipart/form-data">

<input type="hidden" name="post_action" value="add_version">
<input type="hidden" name="parent" value="{?$law.id?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Добавить новую версию документа{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Версия:{?/t?} </td>
    <td>
    <input style="width: 300px" type="file" name="material" value="">
    <input type="Submit" name="Submit" value="{?t?}Добавить{?/t?}">
    </td>
</tr>
</table>
</form-->

<form method="POST" enctype="multipart/form-data">
<input type="hidden" name="id" value="{?$law.id?}">
<input type="hidden" name="filename" value="{?$law.filename?}">
<input type="hidden" name="current_version" value="{?$law.current_version?}">

<input type="hidden" name="post_action" value="edit">

<table width=100% class=main cellspacing=0>
<tr><th colspan=2>{?t?}Редактировать документ{?/t?}</th></tr>
<tr>
    <td>{?t?}Название документа:{?/t?} <font color=red>*</font></td>
    <td><input style="width: 300px" type="text" name="title" value="{?$law.title?}"></td>
</tr>
<tr style="display: none;">
    <td>{?t?}Автор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="author" value="{?$law.author?}"></td>
</tr>
<tr style="display: none;">
    <td>{?t?}Инициатор:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="initiator" value="{?$law.initiator?}"></td>
</tr>
<tr>
    <td>{?t?}Краткое описание (аннотация){?/t?}: </td>
    <td><textarea name="annotation" style="width: 300px;">{?$law.annotation?}</textarea></td>
</tr>
<tr>
    <td>{?t?}Тип:{?/t?} </td>
    <td>
    <select name="type">
    {?html_options options=$types selected=$law.type?}
    </select>
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Регион применения:{?/t?} </td>
    <td>
    <select name="region">
    {?html_options options=$regions selected=$law.region?}
    </select>
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Область применения:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="area_of_application" value="{?$law.area_of_application?}"></td>
</tr>
<tr style="display: none;">
    <td>{?t?}Дата создания:{?/t?} </td>
    <td>
    {?html_select_date field_array="create_date" field_order="DMY" start_year="-100" time=$law.create_date?}
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Срок действия:{?/t?} </td>
    <td><input style="width: 300px" type="text" name="expire" value="{?$law.expire?}"></td>
</tr>
<tr style="display: none;">
    <td>{?t?}Дата изменения:{?/t?} </td>
    <td>
    {?html_select_date field_array="modify_date" field_order="DMY" start_year="-100"?}
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Причина редактирования:{?/t?} </td>
    <td><textarea name="edit_reason" style="width: 300px;">{?$law.edit_reason?}</textarea></td>
</tr>
<tr style="display: none;">
    <td>{?t?}Уровень доступа:{?/t?} </td>
    <td>
    <select name="access_level">
        {?php?}
        global $law;
        for($i=0;$i<=10;$i++) {

            echo "<option value=\"$i\"";
            if ($i==$law['access_level']) echo " selected ";
            echo "> $i";

        }
        echo "<option value=\"0\"";
        if ($law['access_level']==0) echo " selected ";
        echo "> 0";
        {?/php?}
    </select>
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Материал:{?/t?} </td>
    <td>
    <input style="width: 300px" type="file" name="material" value="">
    </td>
</tr>
<tr style="display: none;">
    <td>{?t?}Текстовой вариант{?/t?} (.txt): </td>
    <td>
    <input style="width: 300px" type="file" name="index" value="">
    </td>
</tr>
{?if $law.versions?}
<tr style="display: none;">
    <td>{?t?}Версии:{?/t?} </td>
    <td>
    <table border=0 cellpadding=0 cellspacing=0>
    {?foreach from=$law.versions item=v?}
    <tr>
    <td><input type="radio" name="current_version_id" value="{?$v.id?}" {?if $v.current_version?}checked{?/if?}></td>
    <td>{?$v.upload_date|date_format:"%d.%m.%Y, %H:%M:%S"?}</td>
    <td>
    {?if $v.filename?}
    <a href="{?$sitepath?}laws{?$v.filename?}" target=_blank><img border=0 src="{?$sitepath?}images/icons/save.gif"></a>
    {?/if?}
    </td>
    <td>
    {?if $v.id!=$law.id?}
    <a onClick="if (confirm('{?t?}Удалить версию?{?/t?}')) return true; else return false;" href="{?$sitepath?}laws.php?action=delete&id={?$v.id?}&page={?$page?}&sort={?$sort?}"><img alt="{?t?}Удалить версию{?/t?}" border=0 src="images/icons/delete.gif"></a>
    {?/if?}
    </td>
    </tr>
    {?/foreach?}
    </table>
    </td>
</tr>
{?/if?}
<tr style="display: none;">
    <td colspan=2>{?t?}Категории{?/t?}</td></tr>
<tr style="display: none;">
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
{?if $law.cats?}
    {?foreach from=$law.cats item=i?}
    <option value="{?$i.catid?}">{?$i.name?}</option>
    {?/foreach?}
{?/if?}
    </select>
    </td></tr>
    </table>
    </td>
</tr>
</table>
<p align=right><input type="Submit" name="Submit" value="{?t?}Сохранить{?/t?}" onClick="select_list_select_all('categories');">
<input type="button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}laws.php?page={?$page?}&sort={?$sort?}'">
<p>
<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
</form>
