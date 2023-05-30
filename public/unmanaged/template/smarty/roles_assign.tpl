{?include file="all_header.tpl"?}

{?php?}
echo ph('{?t?}Назначение роли пользователям{?/t?} ('.$GLOBALS['role_name'].')');
$GLOBALS['controller']->captureFromOb(CONTENT);
$GLOBALS['controller']->setHeader(_('Назначение роли пользователям').' ('.$GLOBALS['role_name'].')');
{?/php?}

{?include file="roles_errors.tpl"?}

<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<script type="text/javascript">
<!--
{?$sajax_javascript?}
//-->
</script>
<form action="{?$sitepath?}admin/roles.php" method="POST" onSubmit="select_list_select_all('people2role'); select_list_select_all('people')">
<input type="hidden" name="post_action" value="post_assign">
<input type="hidden" name="pmid" value="{?$pmid?}">
<table width=100% class=main cellspacing=0>
<tr><th>{?t?}Назначение роли пользователям{?/t?}</th></tr>
<tr>
    <td>
    <table width=100% border=0>
    <tr>
    <td width=50% valign="bootom" nowrap="nowrap">
    {?t?}Все пользователи{?/t?}<br>
    <input type="button" value="{?t?}Все{?/t?}" style="height: 18px; width: 15%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');">&nbsp;
    <input type="text" name="search_people" id="search_people" value="{?$search?}" style="width: 80%; color: #888;" onFocus="this.value=''; this.style.color='#000'" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);">
    <div id="people_container" style="padding-top: 5px">
    <select size=10 id="people" name="people[]" multiple style="width:100%;">
    {?$people?}
    </select>
    </div>
    </td>
    <td valign="middle" align=middle>
        <input type="button" value=">>" onClick="select_list_move('people','people2role','select_list_cmp_by_text');">
        <input type="button" value="<<" onClick="select_list_move('people2role','people','select_list_cmp_by_text');">
    </td>
    <td width=50% valign="bottom">
    {?t?}Пользователи, которым назначена данная роль{?/t?}
    <div id="people2role_container">
    <select size=10 id="people2role" name="people2role[]" multiple style="width: 100%">
    {?$people2role?}
    </select>
    </div>
    </td></tr>
    </table>
    </td>
</tr>
</table>

<p align=right>
<input type="Submit" name="Submit" value="{?t?}Сохранить{?/t?}"  onClick="select_list_select_all('people2role'); select_list_select_all('people');">
<input type="button" name="Cancel" value="{?t?}Отмена{?/t?}" onClick="document.location.href='{?$sitepath?}admin/roles.php'">
</p>
</form>

{?php?}
$GLOBALS['controller']->captureStop(CONTENT);
{?/php?}

{?include file="all_header.tpl"?}
