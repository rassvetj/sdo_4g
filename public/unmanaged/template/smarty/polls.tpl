{?if $page.links?}
<table width=100% class=main cellspacing=0>
    <tr>
        <td align=center>{?$page.links?}</td>
    </tr>
</table>
{?/if?}

<table width=100% class=main cellspacing=0>
    <tr>
        <th align=center>{?t?}Название{?/t?}</th>
        <th align=center>{?t?}Начало{?/t?}</th>
        <th align=center>{?t?}Окончание{?/t?}</th>
        <th></th>
    </tr>
    {?if $polls?}
    {?foreach from=$polls item=poll?}
    {?if !$poll->attributes.deleted?}
    <tr>
        <td>{?$poll->attributes.name|escape?}</td>
        <td>{?$poll->rusDate($poll->attributes.begin,'d.m.Y')|escape?}</td>
        <td>{?$poll->rusDate($poll->attributes.end,'d.m.Y')|escape?}</td>
        <td><a href="polls.php?action=edit&id={?$poll->attributes.id?}">{?$icon_edit?}</a> <a onclick="if (confirm('{?t?}Удалить?{?/t?}')) return true; return false;" href="polls.php?action=delete&id={?$poll->attributes.id?}">{?$icon_delete?}</a></td>
    </tr>
    {?/if?}
    {?/foreach?}
    {?else?}
    <tr><td colspan=4 align=center>{?t?}не найдено{?/t?}</td></tr>
    {?/if?}
</table>

{?if $page.links?}
<table width=100% class=main cellspacing=0>
    <tr>
        <td align=center>{?$page.links?}</td>
    </tr>
</table>
{?/if?}