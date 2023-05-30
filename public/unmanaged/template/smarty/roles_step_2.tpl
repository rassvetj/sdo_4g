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

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>

<form action="{?$sitepath?}admin/roles.php?action={?$action?}&step=3" method="POST" onSubmit="select_list_select_all('need_actions');">
<input type="hidden" name="post_action" value="post_step_2">
{?if $necessary_actions?}
{?foreach from=$necessary_actions key=k item=v?}
<input type="hidden" name="need_actions[]" value="{?$k?}">
{?/foreach?}
{?/if?}
<table width=100% class=main cellspacing=0>
<!--<tr><th>{?if $action=='add'?}{?t?}Создание новой роли{?/t?}{?else?}{?t?}Редактирование роли{?/t?}{?/if?}</th></tr>-->
<tr>
    <td>
    <table width=100% border=0>
    <tr>
    <td width=50%>
    {?t?}Функции, доступные базовой роли{?/t?}
    <select size=10 id="roles_actions" name="actions" multiple style="width:100%">
    {?if $actions?}
    {?foreach from=$actions key=k item=v?}
        <option parent={?if strlen($k)==3?}true{?else?}false{?/if?} value="{?$k?}"> {?$v?}</options>
    {?/foreach?}
    {?/if?}
    </select>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move('roles_actions','need_actions','select_list_cmp_by_value');">
        <input type="button" value="<<" onClick="select_list_move('need_actions','roles_actions','select_list_cmp_by_value')">
    </td>
    <td width=50%>
    {?t?}Функции, доступные производной роли{?/t?}
    <select size=10 id="need_actions" name="need_actions[]" multiple style="width: 100%">
    {?if $need_actions?}
    {?foreach from=$need_actions key=k item=v?}
        <option parent={?if strlen($k)==3?}true{?else?}false{?/if?} value="{?$k?}"> {?$v?}</options>
    {?/foreach?}
    {?/if?}
    </select>
    </td></tr>
    </table>
    </td>
</tr>
</table>
<!--p align=right>
<input type="button" name="Backward" value="<< {?t?}Назад{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php?action={?$action?}&step=1'">
<input type="button" name="Cancel" value="{?t?}На главную{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php'">
<input type="Submit" name="Submit" value="{?t?}Готово{?/t?}"  onClick="select_list_select_all('need_actions');">
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
