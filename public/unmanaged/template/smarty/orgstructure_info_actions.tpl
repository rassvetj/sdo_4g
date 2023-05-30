{?if ($smarty.get.checked_items_actions == 'add_mark') || ($smarty.get.checked_items_actions == 'delete_mark') ?}
<form action="orgstructure_info.php" method="POST">
<input type="hidden" name="checked_items_actions" value="{?$smarty.get.checked_items_actions?}"/>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}{?if $smarty.get.checked_items_actions == 'add_mark'?}Добавить виды оценки{?else?}Удалить виды оценки{?/if?}{?/t?}</th>
</tr>
<tr>
    <td>
<select multiple name="roles[]">
{?foreach from=$roles item=role?}
    <option value="{?$role.id?}">{?$role.name|escape?}</option>
{?/foreach?}
</select>
    </td>
</tr>
<tr>
    <td colspan="2">
    {?php?}echo okbutton();{?/php?}
    </td>
</tr>
</table>
</form>
{?/if?}