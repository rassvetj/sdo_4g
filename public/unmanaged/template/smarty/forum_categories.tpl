{?if $BLANK?}
<table width="100%" align="center" cellpadding="0" cellspacing="0">
    <tr>
        <td>
{?/if?}

{?if $MODERATE?}
    <div style='padding-bottom: 5px;'>
        <div style='float: left;'><img src='{?$sitepath?}images/icons/small_star.gif'>&nbsp;</div>
        <div><a target="_top" href='{?$sitepath?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?action=create_category' style='text-decoration: none;'>{?t?}создать категорию{?/t?}</a></div>
    </div>
{?/if?}

<table width=100% class=main cellspacing=0>
	<tr>
		<th>{?t?}Категории{?/t?}</th>
		<th>{?t?}Тем{?/t?}</th>
		<th>{?t?}Обновление{?/t?}</th>
		{?if $MODERATE?}<th>{?t?}Действия{?/t?}</th>{?/if?}
	</tr>
{?if $categories?}
	{?foreach from=$categories item=c?}
	<tr>
		<td width=100%><b><a href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?category={?$c.id?}">{?$c.name?}</a></b>
		{?if $c.course?}<br>{?$c.course?}{?/if?}
		</td>
		<td nowrap align=center>{?$c.threads?}</td>
		<td nowrap align=center>{?$c.create_date|date_format:"%H:%M:%S %d.%m.%Y"?}</td>
  		{?if $MODERATE?}
		<td nowrap>
			<a target="_top" href="{?$SITEPATH?}forum/index/index/subject/{?$SUBJECT?}/subject_id/{?$SUBJECT_ID?}/?action=edit_category&amp;id={?$c.id?}"><img border=0 title="{?t?}Редактировать категорию{?/t?}" src="{?$SITEPATH?}/images/icons/edit.gif"></a>
			<a target="_top" onClick="if (confirm('{?t?}Вы действительно желаете удалить категорию?{?/t?}')) return true; else return false;" href="{?$SITEPATH?}forum/index/index/?action=delete_category&amp;id={?$c.id?}"><img border=0 title="{?t?}Удалить категорию{?/t?}" src="{?$SITEPATH?}/images/icons/delete.gif"></a>
			</td>
		{?/if?}
	</tr>
	{?/foreach?}
{?else?}
	<tr>
		<td colspan=99 align=center>{?t?}не найдено{?/t?}</td>
	</tr>
{?/if?}
</table>

{?if $BLANK?}
        </td>
    </tr>
</table>
{?/if?}