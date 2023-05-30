<script type="text/javascript" language="JavaScript" src="js/roles.js"></script>
<script type="text/javascript" language="JavaScript">
<!--
function checkForm() {
   var elm;

   if (elm = document.getElementById('poll_name')) {
       if ((elm.value == '') && (!elm.disabled)) {
           alert('{?t?}Введите название аттестации{?/t?}');
           return false;
       }
   }

   return true;
}
//-->
</script>

<form action="" method="POST">
<input type="hidden" name="action" value="assign" id="action">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=99>{?t?}Назначение аттестации{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название аттестации{?/t?}</td>
    <td><input type="text" value="" style="width: 300px;" name="poll_name" id="poll_name">
    </td>
</tr>
<tr>
    <td>{?t?}Комментарий к анкете{?/t?}</td>
    <td><textarea name="description" rows="5" style="width: 100%"></textarea></td>
</tr>
<tr>
    <td>{?t?}Начало{?/t?}</td>
    <td>{?html_select_date end_year="+5" field_array="start" field_order="DMY"?}</td>
</tr>
<tr>
    <td>{?t?}Окончание{?/t?}</td>
    <td>{?html_select_date end_year="+5" field_array="end" field_order="DMY" time=$date_end?}</td>
</tr>
<tr>
    <td colspan=2>
    <table width=100% border=0>
    <td colspan="3">
	    {?t?}Аттестация включает в себя этапы{?/t?}:
    </td>
    <tr>
        <td width=50% align=center>
            <select size=7 id="all_states" name="all_states[]" multiple style="width: 300px">
            {?if $all_states?}
                {?foreach from=$all_states key=k item=i?}
                    <option value="{?$k?}" title="{?$i?}"> {?$i?}</option>
                {?/foreach?}
            {?/if?}
            </select>
        </td>
        <td valign=middle align=middle>
            <input type="button" value=">>" onClick="select_list_move('all_states','states','select_list_cmp_by_value')">
            <input type="button" value="<<" onClick="select_list_move('states','all_states','select_list_cmp_by_value')">
        </td>
            <td width=50% align=center>
            <select size=7 id="states" name="data[states][array][]" multiple style="width: 300px">
            {?if $states?}
                {?foreach from=$states key=k item=i?}
                    <option value="{?$k?}" title="{?$i?}"> {?$i?}</option>
                {?/foreach?}
            {?/if?}
            </select>
        </td>
    </tr>
    <tr>
        <td colspan="3">
        <input type="checkbox" value="1" name="sequence" checked readonly>&nbsp;важна последовательность
        </td>
    </tr>
    </table>
</tr>
<input type="hidden" name="subject_soids" value="{?$items?}">
</table>
<table width=100% border=0 cellpadding=10 cellspacing=1>
<tr>
<td align="right" width="100%" class="button-option">
<input type="checkbox" onchange="javascript: document.getElementById('button-ok').innerHTML = (this.checked) ? '{?t?}Далее{?/t?}&nbsp;' : '{?t?}Готово{?/t?}'; document.getElementById('action').value = (this.checked) ? 'assign-detailed' : 'assign'; return true;" class="button-option">&nbsp;настроить параметры 360&deg; отдельно для каждого сотрудника (всего {?$count?})</td>
<td>
<div class="button ok" style="float: right;">
<a onclick="select_list_select_all('states'); if (checkForm()) return eLS.utils.form.submit(this);" href="javascript:void(0);" id="button-ok">Готово</a>
</div>
</td>
</tr>
<input type="hidden" name="subject_soids" value="{?$items?}">
</table>
</form>