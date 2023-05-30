<form action = 'studying_period.php' method='POST' target='_blank'>
{?t?}Курс:{?/t?} <select name='cid' onChange="document.location.href='studying_period.php?cid='+this.value;">{?html_options options=$cids selected=$cid?}</select><br><br> 
<!-- {?t?}Период с{?/t?}&nbsp;&nbsp;{?html_select_date prefix='' field_array=date[from] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$from_timestamp?}
&nbsp;{?t?}по{?/t?}&nbsp;{?html_select_date prefix='' field_array=date[to] field_order=DMY month_format=%m start_year=2005 end_year=2010 time=$to_timestamp?}&nbsp;<br><br>
-->
<select name=weeks>{?$weeks?}</select>

<input type='submit' value='{?t?}Просмотр{?/t?}'>
<input name='view' type='hidden' value='1'>
</form>