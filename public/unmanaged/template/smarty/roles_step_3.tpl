{?include file="all_header.tpl"?}

{?if $action=='add'?}
{?php?}
echo ph('{?t?}Создание новой роли{?/t?} ('.$GLOBALS['s']['roles']['values']['role_name'].')');
$GLOBALS['controller']->setHeader(_('Создание новой роли') . ' ('.$GLOBALS['s']['roles']['values']['role_name'].')');
{?/php?}
{?else?}
{?php?}
echo ph('{?t?}Редактирование роли{?/t?} ('.$GLOBALS['s']['roles']['values']['role_name'].')');
$GLOBALS['controller']->setHeader(_('Редактирование роли') . ' ('.$GLOBALS['s']['roles']['values']['role_name'].')');
{?/php?}
{?/if?}

{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}

{?include file="roles_errors.tpl"?}

<form action="{?$sitepath?}admin/roles.php?action={?$action?}&step=4" method="POST">
<input type="hidden" name="post_action" value="post_step_3">
<table width=100% class=main cellspacing=0>
<!--<tr><th>{?if $action=='add'?}{?t?}Создание новой роли{?/t?}{?else?}{?t?}Редактирование роли{?/t?}{?/if?}</th></tr>-->
{?if $actions?}
{?foreach from=$actions item=v?}
<tr>
    <td>
        <b>{?$v.name?}</b>
        <input type="hidden" name="pages[]" value="{?$v.id?}">
    </td>
</tr>
{?if $v.links?}
   {?foreach from=$v.links item=vv?}
<tr>
    <td><input type="checkbox" {?if isset($role_actions)?}{?if in_array($vv.id,$role_actions)?}checked{?/if?}{?else?}checked{?/if?} name="actions[]" value="{?$vv.id?}">
    {?$vv.name?}</td>
</tr>
   {?/foreach?}
{?/if?}
{?if $v.tabs?}
   {?foreach from=$v.tabs item=vv?}
<tr>
    <td><input type="checkbox" {?if isset($role_actions)?}{?if in_array($vv.id,$role_actions)?}checked{?/if?}{?else?}checked{?/if?} name="actions[]" value="{?$vv.id?}">
    {?$vv.name?}</td>
</tr>
   {?/foreach?}
{?/if?}
{?if $v.options?}
   {?foreach from=$v.options item=vv?}
<tr>
    <td><input type="checkbox" {?if isset($role_actions)?}{?if in_array($vv.id,$role_actions)?}checked{?/if?}{?else?}checked{?/if?} name="actions[]" value="{?$vv.id?}">
    {?$vv.name?}</td>
</tr>
   {?/foreach?}
{?/if?}
{?/foreach?}
{?/if?}
</table>

<!--p align=right>
<input type="button" name="Backward" value="<< {?t?}Назад{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php?action={?$action?}&step=2'">
<input type="button" name="Cancel" value="{?t?}На главную{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php'">
<input type="Submit" name="Submit" value="{?t?}Вперед{?/t?} >>">
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
