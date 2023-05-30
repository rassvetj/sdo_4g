<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>
<script type="text/javascript">
<!--
{?$sajax_javascript?}
//-->
</script>
<form method="POST" onSubmit="select_list_select_all('users'); select_list_select_all('all_users');">
<input type="hidden" name="action" value="assign">
<table width=100% cellspacing=0 class=main>
    <tr>
    <td>
    <table width=100% border=0>
    <tr>    
    <td width=50% valign="bottom" nowrap="nowrap">
    {?t?}Все пользователи{?/t?}:<br>    
    <input type="button" value="{?t?}Все{?/t?}" style="height: 18px; width: 15%" onClick="if (elm = document.getElementById('search_people')) elm.value='*'; get_user_select('*');">
    <input type="text" name="search_people" id="search_people" value="{?$search?}" style="width: 80%" onFocus="showHideString(this);" onBlur="showHideString(this);" onKeyUp="if (typeof(filter_timeout)!='undefined') clearTimeout(filter_timeout); filter_timeout = setTimeout('get_user_select(\''+this.value+'\');',1000);">
    <div id="people" style="padding-top: 5px">
    <select size=10 id="all_users" name="del_users[]" multiple style="width:100%">
    {?$all_people?}
    </select>
    </div>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move('all_users','users','select_list_cmp_by_text');">
        <input type="button" value="<<" onClick="select_list_move('users','all_users','select_list_cmp_by_text');">
    </td>
    <td width=50% valign="bottom">
    {?t?}Пользователи, которым назначена данная роль{?/t?}:
    <div id="people_used">
    <select size=10 id="users" name="need_users[]" multiple style="width: 100%">
    {?$users?}
    </select>
    </div>    
    </td>
    </tr></table></td>
    </tr>
</table>
<br>
{?$okbutton?}
</form>
