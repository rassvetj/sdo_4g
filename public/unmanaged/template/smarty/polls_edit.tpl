<form action="" method="POST">
<input type="hidden" name="action" value="update">
<input type="hidden" name="form[id][int]" value="{?$poll->attributes.id?}">
<table width=100% class=main cellspacing=0>
<tr>
    <th colspan=2>{?t?}Редактировать{?/t?}</th>
</tr>
<tr>
    <td>{?t?}Название{?/t?}:</td>
    <td><input name="form[name][string]" type="text" value="{?$poll->attributes.name|escape?}" style="width:300px;"></td>
</tr>
<tr>
    <td>{?t?}Начало{?/t?}:</td>
    <td>{?html_select_date field_array='form[begin][array][date]' time=$poll->attributes.begin field_order='DMY' end_year='+5'?} &nbsp;&nbsp;&nbsp; {?html_select_time field_array='form[begin][array][date]' display_seconds=false time=$poll->unixDate($poll->attributes.begin)?}</td>
</tr>
<tr>
    <td>{?t?}Окончание{?/t?}:</td>
    <td>{?html_select_date field_array='form[end][array][date]' time=$poll->attributes.end field_order='DMY' end_year='+5'?} &nbsp;&nbsp;&nbsp; {?html_select_time field_array='form[end][array][date]' display_seconds=false time=$poll->unixDate($poll->attributes.end)?}</td>
</tr>
<tr>
    <td colspan=2>{?$okbutton?}</td>
</tr>
</table>
</form>