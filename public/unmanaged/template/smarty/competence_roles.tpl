{?if $links?}
<br><table cellspacing=0 class=main width=100%><tr><td align=center>{?$links?}</td></tr></table><br>
{?/if?}
<table width=100% cellspacing=0 class=main>
<tr>
    <th>{?t?}Виды оценок{?/t?}</th>
    <th>{?t?}Критерии{?/t?}</th>
    <th></th>
</tr>
{?if $roles?}
{?foreach from=$roles item=role?}
	<tr>
	    <td>{?$role.name?}</td>
	    <td>
	    {?if $role.competences?}
	        {?foreach from=$role.competences item=i?}
	            {?$i?}<br>
	        {?/foreach?}
	    {?/if?}
	    </td>
	    <td >
		    <a href="{?$SITEPATH?}competence_roles.php?action=edit&id={?$role.id?}">
		    <img alt="{?t?}Редактировать{?/t?}" border=0 src="{?$SITEPATH?}images/icons/edit.gif">
		    </a> &nbsp;
            <a onClick="if (confirm('{?t?}Удалить роль?{?/t?}')) return true; else return false;" href="{?$SITEPATH?}competence_roles.php?action=delete&id={?$role.id?}"><img alt="Удалить роль" border=0 src="{?$SITEPATH?}images/icons/delete.gif"></a>
	    </td>
	</tr>
{?/foreach?}
{?else?}
<tr><td colspan=3 align=center>{?t?}не найдено{?/t?}</td></tr>
{?/if?}
</table>
{?if $links?}
<br><table cellspacing=0 class=main width=100%><tr><td align=center>{?$links?}</td></tr></table><br>
{?/if?}
