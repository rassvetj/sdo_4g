<h3>{?if $disable_simulation?}{?t?}Добавлено{?/t?}{?else?}{?t?}Будет добавлено{?/t?}{?/if?}: {?$count_added?}</h3>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Логин{?/t?}</th>
    <th>{?t?}Фамилия{?/t?}</th>
    <th>{?t?}Имя{?/t?}</th>
    <th>E-Mail</th>
</tr>
{?if is_array($added) && count($added)?}
{?foreach from=$added item=i?}
<tr>
    <td>{?$i.samaccountname.0?}</td>
    <td>{?$i.sn.0?}</td>
    <td>{?$i.givenname.0?}</td>
    <td>{?$i.mail.0?}</td>
</tr>
{?/foreach?}
{?else?}
<tr><td colspan=4>{?t?}Никого{?/t?}</td></tr>
{?/if?}
</table>
<p>
<h3>{?if $disable_simulation?}{?t?}Удалено{?/t?}{?else?}{?t?}Будет удалено{?/t?}{?/if?}: {?$count_deleted?}</h3>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Логин{?/t?}</th>
    <th>{?t?}Фамилия{?/t?}</th>
    <th>{?t?}Имя{?/t?}</th>
    <th>E-Mail</th>
</tr>
{?if is_array($deleted) && count($deleted)?}
{?foreach from=$deleted item=i?}
<tr>
    <td>{?$i.Login?}</td>
    <td>{?$i.LastName?}</td>
    <td>{?$i.FirstName?}</td>
    <td>{?$i.EMail?}</td>
</tr>
{?/foreach?}
{?else?}
<tr><td colspan=4>{?t?}Никого{?/t?}</td></tr>
{?/if?}
</table>
<p>
<h3>{?if $disable_simulation?}{?t?}Изменено{?/t?}{?else?}{?t?}Будет изменено{?/t?}{?/if?}: {?$count_modified?}</h3>
<table width=100% class=main cellspacing=0>
<tr>
    <th>{?t?}Логин{?/t?}</th>
    <th>{?t?}Фамилия{?/t?}</th>
    <th>{?t?}Имя{?/t?}</th>
    <th>E-Mail</th>
</tr>
{?if is_array($modified) && count($modified)?}
{?foreach from=$modified item=i?}
<tr>
    <td>{?$i.samaccountname.0?}</td>
    <td>{?$i.sn.0?}</td>
    <td>{?$i.givenname.0?}</td>
    <td>{?$i.mail.0?}</td>
</tr>
{?/foreach?}
{?else?}
<tr><td colspan=4>{?t?}Никого{?/t?}</td></tr>
{?/if?}
</table>
<p>
<form action="{?$sitepath?}people_import_ad.php" method=POST>
<div align=right>
{?if !$disable_simulation?}
<input type="hidden" name="import_from_ad" value="import_from_ad">
<input type="hidden" name="disable_simulation" value="1">
<input type="hidden" name="domain_name" value="{?$domain_name?}">
<input type="hidden" name="username" value="{?$username?}">
<input type="hidden" name="password" value="{?$password?}">
{?php?}
echo "<a href=\"javascript:void(0)\" onClick=\"document.location.href='".$GLOBALS['sitepath']."';\"><img border=0 src='".$GLOBALS['controller']->view_root->skin_url.'/images/b_cancel.gif'."'></a>";
{?/php?}
{?/if?}
{?php?}
echo "&nbsp;&nbsp;&nbsp;<input type=image class=noborder src='".$GLOBALS['controller']->view_root->skin_url.'/images/ok.gif'."'>";
{?/php?}
</div>
</form>
