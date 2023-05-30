{?if $page.links?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>{?$page.links?}</td>
</tr>
</table>
{?/if?}
<form action="" method="POST">
<div style="padding-bottom: 5px;">
<div style="float: left;">
    <img src="images/icons/small_star.gif"/>
</div>
<div>
    <a style="text-decoration: none;" href="{?$sitepath?}run_list.php?action=add&cid={?$cid?}">{?t?}создать программу{?/t?}</a>
</div>         
</div>
<table width=100% class=main cellspacing=0>
<tr>
    <th></th>
    <th>{?t?}Название{?/t?}</th>
    <th>{?t?}Имя файла{?/t?}</th>
    <th>{?t?}Действия{?/t?}</th>
</tr>
{?if $items?}
    {?foreach from=$items item=item?}
<tr>
    <td width=1%><input type="checkbox" name="run_id[]" value="{?$item->attributes.run_id?}"></td>
    <td>{?$item->attributes.name|escape?}</td>
    <td>{?$item->attributes.path|escape?}</td>
    {?if $perm_edit?}
    <td nowrap width=1%>
        {?if $item->attributes.perm_edit?}
        <a href="{?$sitepath?}run_list.php?action=edit&id={?$item->attributes.run_id?}">{?$icon_edit?}</a>
        <a href="{?$sitepath?}run_list.php?action=delete&id={?$item->attributes.run_id?}" onClick="if (confirm('{?t?}Удалить?{?/t?}')) return true; return false;">{?$icon_delete?}</a>
        {?/if?}
    </td>
    {?/if?}
</tr>
    {?/foreach?}
<tr>
    <td colspan=99 align=right>
    {?t?}Выполнить действие{?/t?}
    <select name="action">
    <option value="copy"> {?t?}копировать{?/t?}</option>
    </select>
    {?t?}в{?/t?}
    <select name="cid">
    {?if $courses?}
        {?foreach from=$courses key=courseId item=title?}
        <option value="{?$courseId?}"> {?$title|escape?}</option>
        {?/foreach?}
    {?/if?}
    </select>
    <br><br>
    {?$okbutton?}
    </td>    
</tr> 
{?else?}
<tr><td align=center colspan=99>{?t?}нет данных для отображения{?/t?}</td></tr>
{?/if?}
</table>
</form>
{?if $page.links?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>{?$page.links?}</td>
</tr>
</table>
{?/if?}
