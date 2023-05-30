<form action = 'schedule_groups.php' method='POST' target='_blank'>
<!--{?t?}Период с{?/t?}&nbsp;&nbsp;{?html_select_date prefix='' field_array=date[from] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$from_timestamp?}
&nbsp;{?t?}по{?/t?}&nbsp;{?html_select_date prefix='' field_array=date[to] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$to_timestamp?}&nbsp;<br><br>-->
<select name=weeks>{?$weeks?}</select>
<input type='submit' value='{?t?}Просмотр{?/t?}'>
<input name='view' type='hidden' value='1'>
<input name='cid' type='hidden' value='{?$cid?}'>
</form>