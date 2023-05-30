<script type="text/javascript" language="JavaScript" src="{?$sitepath?}js/roles.js"></script>

<form method="POST" onSubmit="select_list_select_all('courses'); select_list_select_all('all_courses');">
<input type="hidden" name="action" value="assign">
<input type="hidden" name="MID" value="{?$MID?}">
<table width=100% class=main cellspacing=0>
    <tr>
    <td width=50%>
    {?t?}Все курсы{?/t?}:
    <select size=10 id="all_courses" name="del_courses[]" multiple style="width:100%">
    {?if $all_courses?}
    {?foreach from=$all_courses key=k item=v?}
        <option value="{?$k?}"> {?$v?}</options>
    {?/foreach?}
    {?/if?}
    </select>
    </td>
    <td valign=middle align=middle>
        <input type="button" value=">>" onClick="select_list_move('all_courses','courses','select_list_cmp_by_text');">
        <input type="button" value="<<" onClick="select_list_move('courses','all_courses','select_list_cmp_by_text');">
    </td>
    <td width=50%>
    {?t?}Курсы, назначенные данному слушателю{?/t?}:
    <select size=10 id="courses" name="need_courses[]" multiple style="width: 100%">
    {?if $person_courses?}
    {?foreach from=$person_courses key=k item=v?}
        <option value="{?$k?}" {?if $chains[$k]>0?}dontmove="dontmove" style="background: #EEEEEE"{?/if?}> {?$v?}</option>
    {?/foreach?}
    {?/if?}
    </select>
    </td></tr>
</table><br>
{?$okbutton?}
</form>
