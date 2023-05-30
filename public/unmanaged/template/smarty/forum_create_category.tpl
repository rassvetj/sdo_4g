{?if $BLANK?}
<table width="100%" align="center" cellspacing="0">
    <tr>
        <td>
{?/if?}

{?if $MODERATE?}
<form name="{?$action?}" action="" method="POST">
<input name="action" type="hidden" value="{?if !$action?}create_category{?/if?}">
	<table width=100% class=main cellspacing=0>
		<tr>
			<td>{?t?}Название{?/t?} </td>
			<td><input name="data[name][string]" type="text" value="{?$data.name|escape?}" style="width: 250px"></td>
		</tr>
		<!--  
		<tr>
			<td>{?t?}Краткое описание{?/t?} </td>
			<td><textarea name="data[description][string]" style="width: 300px">{?$data.description|escape?}</textarea></td>
		</tr>
		-->
		{?if ($PERM==2) && $COURSES?}
		<tr>
			<td>{?t?}Курс{?/t?} </td>
			<td>
			<select size="1" name="data[cid][int]">
				<option value="0"> {?t?}Все{?/t?}</option>
			{?foreach from=$COURSES key=cid item=name?}
				<option value="{?$cid?}"> {?$name?}</option>
			{?/foreach?}
			</select>
			</td>
		</tr>
		{?/if?}
	</table>
    <table border="0" cellspacing="5" cellpadding="0" width="100%">
          <tr>
            <td align="right" width="99%">
                {?$OKBUTTON?}
            </td>
            <td align="right" width="1%">
            <div style='float: right;' class='button'><a href='{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/'>{?t?}Отмена{?/t?}</a></div><input type='button' value='{?t?}отмена{?/t?}' style='display: none;'/><div class='clear-both'></div>
            </td>
          </tr>
    </table>
</form>
{?/if?}

{?if $BLANK?}
        </td>
    </tr>
</table>
{?/if?}