{?include file="all_header.tpl"?}
{?if $action=='add'?}
{?php?}
echo ph(_('Создание новой роли'));
$GLOBALS['controller']->setHeader(_('Создание новой роли'));
{?/php?}
{?else?}
{?php?}
echo ph('{?t?}Редактирование роли{?/t?}');
$GLOBALS['controller']->setHeader(_('Редактирование роли'));
{?/php?}
{?/if?}
{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?include file="roles_errors.tpl"?}

<form action="{?$sitepath?}admin/roles.php?action={?$action?}&step=2" method="POST">
<input type="hidden" name="post_action" value="post_step_1">
<table width=100% class=main cellspacing=0>
<!--<tr><th colspan=2>{?if $action=='add'?}{?t?}Создание новой роли{?/t?}{?else?}{?t?}Редактирование роли{?/t?}{?/if?}</th></tr>-->
<tr><td>{?t?}Базовая роль{?/t?}{?$values.vase_role?}</td>
<td>
<select name="base_role">
{?html_options options=$base_roles_lang selected=$values.base_role?}
</select>
{?$tooltip->display('base_role')?}
</td></tr>
<tr><td>{?t?}Название роли{?/t?}</td>
<td>
<input type="text" style="width: 150px;" name="role_name" value="{?if $values?}{?$values.role_name|escape?}{?/if?}">
{?$tooltip->display('role_name')?}
</td></tr>
<tr><td colspan=2><input type="checkbox" name="default" value="1" {?if $values.default?}checked{?/if?}> {?t?}всем новым{?/t?}&nbsp;
{?$tooltip->display('all_new')?}
</td></tr>
</table>

<!-- p align=right>
<input type="button" name="Backward" value="<< {?t?}Назад{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php'">
<input type="button" name="Cancel" value="{?t?}На главную{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php'">
<input type="Submit" name="Submit" value="{?t?}Вперёд{?/t?} >>">
</p-->
<table align="right">
    <tr>
        <td>{?$backButton?}</td>
        <td>{?$cancelButton?}</td>
        <td>{?$submitButton?}</td>
    </tr>
</table>
</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_header.tpl"?}
