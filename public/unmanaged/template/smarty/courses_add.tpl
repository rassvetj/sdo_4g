<form action="" method="POST">
    <input type="hidden" name="Action" value="reg"/>
    <input type="hidden" name="day" value="{?$date1.d?}"/>
    <input type="hidden" name="month" value="{?$date1.m?}"/>
    <input type="hidden" name="year" value="{?$date1.y?}"/>
    <input type="hidden" name="day2" value="{?$date2.d?}"/>
    <input type="hidden" name="month2" value="{?$date2.m?}"/>
    <input type="hidden" name="year2" value="{?$date2.y?}"/>
    <input type="hidden" name="longtime" value="{?$longtime?}"/>
	<table width=100% class=main cellspacing=0>
		<tr>
			<td>{?t?}Название{?/t?} </td>
			<td><input name="Title" type="text" value="" style="width: 250px"></td>
		</tr>
	</table>
    <table border="0" cellspacing="5" cellpadding="0" width="100%">
          <tr>
            <td align="right" width="99%">
                {?php?}echo okbutton();{?/php?}
            </td>
            <td align="right" width="1%">
            <div style='float: right;' class='button'><a href='{?$sitepath?}courses.php4'>{?t?}Отмена{?/t?}</a></div><input type='button' value='{?t?}отмена{?/t?}' style='display: none;'/><div class='clear-both'></div>
            </td>
          </tr>
    </table>
</form>