{?include file="all_header.tpl"?}

{?php?}
echo ph(_('Роли пользователей'));
{?/php?}

<input type="button" name="add" value="{?t?}Создать{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php?action=add'">
<p>
{?php?}
$GLOBALS['controller']->captureFromOb(CONTENT);
{?/php?}
<div style='padding-bottom: 5px;'>
    <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
    <div><a href='{?$sitepath?}admin/roles.php?action=add' style='text-decoration: none;'>{?t?}создать роль{?/t?}</a></div>
</div>

<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Роль{?/t?}</th>
    <th nowrap>{?t?}Базовая роль{?/t?}</th>
    <th width="100%">{?t?}Доступные функции{?/t?}</th>
    <th>{?t?}Действия{?/t?}</th>
</tr>
{?if $roles?}
{?foreach from=$roles item=v key=k?}
<tr>
    <td>{?if $v.default?}<b title="{?t?}Всем новым{?/t?}">{?/if?}{?$v.name?}{?if $v.default?}</b>{?/if?}</td>
    <td>{?$v.type?}</td>
    <td>
    {?if is_array($v.perms)?}
        <span id="plus_{?$k?}" style="display: block" onClick="$('#new_{?$k?}').show(); $('#plus_{?$k?}').hide(); $('#minus_{?$k?}').show();">
            <span class="cDisabled"><span title="{?t?}Открыть список функций{?/t?}" class="webdna" style="cursor:pointer;">&#8594;</span></span>
        </span>
        <span id="minus_{?$k?}" style="display: none;" onClick="$('#new_{?$k?}').hide(); $('#minus_{?$k?}').hide(); $('#plus_{?$k?}').show();">
            <span class="cDisabled"><span title="{?t?}Закрыть список функций{?/t?}" class="webdna" style="cursor:pointer;">&#8595;</span></span>
        </span>
		<span id="new_{?$k?}" style="display: none;">
        {?foreach from=$v.perms item=vv?}
        {?if $vv && $roles_names[$vv]?}{?$roles_names[$vv]?}<br>{?/if?}
        {?/foreach?}
        </span>
    {?/if?}
    </td>
    <td nowrap>
    <a href="{?$sitepath?}admin/roles.php?action=edit&id={?$v.pmid?}" title="{?t?}Редактировать роль{?/t?}"><img border=0 src="{?$sitepath?}images/icons/edit.gif"></a>
    <a href="{?$sitepath?}admin/roles.php?action=assign&id={?$v.pmid?}" title="{?t?}Назначить роль пользователям{?/t?}"><img border=0 width=15 src="{?$sitepath?}images/icons/people.gif"></a>
    <!--a target=_blank href="{?$sitepath?}admin/roles.php?action=info&id={?$v.pmid?}" title="{?t?}Создать руководство пользователя{?/t?}"><img border=0 src="{?$sitepath?}images/icons/reference.gif"></a-->
    <a onClick="if (confirm('{?t?}Вы действительно желаете удалить роль? Восстановить будет невозможно!{?/t?}')) return true; else return false;" href="{?$sitepath?}admin/roles.php?action=delete&id={?$v.pmid?}" title="{?t?}Удалить роль{?/t?}"><img border=0 src="{?$sitepath?}images/icons/delete.gif"></a>
    </td>
</tr>
{?/foreach?}
{?else?}
<tr>
    <td colspan=4 align=center>{?t?}нет существующих ролей{?/t?}</td>
</tr>
{?/if?}
</table>

<p>

<!-- form action="{?$sitepath?}admin/roles.php?action=add" method="POST">
<input name="post_action" type="hidden" value="post_new">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Создание новой роли{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название роли{?/t?} </td><td><input name="role_name" type="text" value=""></td>
</tr>
</table>
<br>
{?$okbutton?}
</form -->

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}
{?include file="all_header.tpl"?}
