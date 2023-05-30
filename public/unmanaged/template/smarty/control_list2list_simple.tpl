<table width=100% cellspacing=0 class=main>
    <tr>
        <td width=50%>
            {?$list1_title|escape?}<br>
            <select name="{?$list1_name?}[]" id="{?$list1_name?}" size=5 multiple style="width:100%;">
            {?if $list1_data?}
            {?foreach from=$list1_data key=key item=value?}
                <option value="{?$key?}"> {?$value|escape?}</option>
            {?/foreach?}
            {?/if?}
            </select>
        </td>
        <td nowrap>
            <input type="button" value=">>" onClick="select_list_move('{?$list1_name?}','{?$list2_name?}','select_list_cmp_by_text');"><br>
            <input type="button" value="<<" onClick="select_list_move('{?$list2_name?}','{?$list1_name?}','select_list_cmp_by_text');">
        </td>
        <td width=50%>
            {?$list2_title|escape?}<br>
            <select name="{?$list2_name?}[]" id="{?$list2_name?}" size=5 multiple style="width:100%;">
            {?if $list2_data?}
            {?foreach from=$list2_data key=key item=value?}
                <option value="{?$key?}"> {?$value|escape?}</option>
            {?/foreach?}
            {?/if?}
            </select>
        </td>
    </tr>
</table>