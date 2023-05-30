<div style='padding-bottom: 5px;'>
    <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
    <div><a href='{?$sitepath?}chains.php?action=add' style='text-decoration: none;'>{?t?}создать цепочку согласования{?/t?}</a></div>
</div>
<table width=100% cellspacing=0 class=main>
    <tr>
        <th>{?t?}Название{?/t?}</th>
        <th>{?t?}Состав{?/t?}</th>
        <th>{?t?}Действия{?/t?}</th>
    </tr>
{?if $chains?}
    {?foreach from=$chains item=chain?}
    <tr>
        <td>{?$chain.name?}</td>
        <td>
        {?if $chain.chain?}
            {?foreach from=$chain.chain item=i?}
            {?$i?}<br>
            {?/foreach?}
        {?/if?}
        </td>
        <td>
        <a href="{?$sitepath?}chains.php?action=edit&id={?$chain.id?}">
        <img title="{?t?}Редактировать цепочку согласования{?/t?}" border=0 src="images/icons/edit.gif"></a>
        <a href="{?$sitepath?}chains.php?action=delete&id={?$chain.id?}" onClick="if (confirm('{?t?}Вы действительно желаете удалить цепочку согласования?{?/t?}')) return true; else return false;">
        <img title="{?t?}Удалить цепочку согласования{?/t?}" border=0 src="images/icons/delete.gif"></a>
        </td>
    </tr>
    {?/foreach?}
{?else?}
<tr><td colspan="3" class="nodata">{?t?}Нет данных для отображения{?/t?}</td></tr>
{?/if?}
</table>