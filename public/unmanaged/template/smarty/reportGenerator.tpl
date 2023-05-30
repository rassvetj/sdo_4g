{?include file="common/add_link.tpl"?}
{?if $page.links?} 
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>{?$page.links?}</td>
</tr>
</table>
{?/if?}
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Название шаблона{?/t?}</th>
</tr>
{?if $items?}
    {?foreach from=$items key=id item=name?}
        <tr>    
            <td>{?$name|escape?}</td>    
            {?if $perm_edit?}
                <td nowrap width=1%>
                    {?if $perm_edit?}
                    <a href="{?$sitepath?}reportGenerator.php?action=edit&id={?$id?}">{?$icon_edit?}</a>
                    <a href="{?$sitepath?}reportGenerator.php?action=delete&id={?$id?}" onClick="if (confirm('{?t?}Удалить?{?/t?}')) return true; else return false;">{?$icon_delete?}</a>
                    {?/if?}
                </td>
            {?/if?}
        </tr>
    {?/foreach?}
{?else?}
<tr><td align=center colspan=99>{?t?}шаблоны отсутствуют{?/t?}</td></tr>
{?/if?}
</table>
{?if $page.links?}
<table width=100% class=main cellspacing=0>
<tr>
    <td align=center>{?$page.links?}</td>
</tr>
</table>
{?/if?}
