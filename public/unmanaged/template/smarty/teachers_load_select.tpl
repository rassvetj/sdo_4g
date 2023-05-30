<form action = 'teachers_load.php' method='POST' target='_self'>
{?t?}Преподаватель:{?/t?} <select name='mid'><option value='0'> -- {?t?}все{?/t?} -- </option>{?html_options options=$teachers selected=$teacher_selected?}</select><br><br>
{?t?}Период с{?/t?}&nbsp;&nbsp;{?html_select_date prefix='' field_array=date[from] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$from_timestamp?}
&nbsp;{?t?}по{?/t?}&nbsp;{?html_select_date prefix='' field_array=date[to] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$to_timestamp?}&nbsp;<br><br>
<input type='submit' value='{?t?}Просмотр{?/t?}'>
<input name='view' type='hidden' value='1'>
</form>
<br>