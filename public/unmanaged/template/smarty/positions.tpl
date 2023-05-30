<tr id="{?$item->display_attributes.id?}" {?$item->display_attributes.hidden?}>
	<td>
	{?if $item->attributes.type eq $smarty.const.TYPE_ITEM?}    
		<a style='display:none;' id='{?$item->display_attributes.id?}_minus' href='javascript:void(0);' onClick="removeTreeElementsByPrefix('{?$item->display_attributes.id?}');"><img align=absmiddle border=0 src="{?$sitepath?}images/ico_minus.gif"></a>
		<a id='{?$item->display_attributes.id?}_plus' href='javascript:void(0);' onClick="putTreeElementsByPrefix('{?$item->display_attributes.id?}','table-row');"><img align=absmiddle border=0 src="{?$sitepath?}images/ico_plus.gif"></a>                        
	{?/if?}
	</td>
	{?if $smarty.const.STRUCTURE_OF_ORGAN_PERM_EDIT_ON?}
	<td>
	{?if $item->person || ($item->attributes.type eq $smarty.const.TYPE_ITEM)?}
		<input type=checkbox id='che_{?$item->attributes.soid?}'  name=che[{?$item->attributes.soid?}] {?$item->display_attributes.checked?}>
	{?/if?}
	</td>
	{?/if?}
	{?if $item->display_attributes.message?}
	<td>
		{?$item->display_attributes.message?}
	</td>
	{?/if?}
	<td>
		<img border=0 align=absmiddle alt="{?$item->attributes.type?}" src="{?$sitepath?}images/icons/positions_type_{?$item->attributes.type?}.gif">
	</td>
	{?assign var="width" value=$item->display_attributes.nesting*2?}
	{?assign var="space" value='&nbsp;'?}
	<td>
	{?$space|indent:$width:"."?}<a href="javascript:void(0);" onClick="wopen('soid_info.php?soid={?$item->attributes.soid?}','soid_{?$item->attributes.soid?}', '450', '350')" style="font-size:11px;"><span class="{?$item->display_attributes.class?}">{?$item->attributes.name?}</span></a>&nbsp;
	</td>
	<td>{?$item->attributes.code?}</td>
	<td>
	{?if $item->person?}
		<a href="javascript:void(0);" onClick="wopen('userinfo.php?mid={?$item->person->attributes.MID?}','user_{?$item->person->attributes.MID?}', '400', '300')" style="font-size:11px;">{?$item->person->attributes.LastName?}&nbsp;{?$item->person->attributes.FirstName?}&nbsp;{?$item->person->attributes.Patronymic?}</a>
	{?/if?}
	</td>
	<td>
		{?$item->attributes.info?}
	</td>
	<td  align='left'>
	{?if ($item->attributes.type != $smarty.const.TYPE_ITEM) && $smarty.const.STRUCTURE_OF_ORGAN_PERM_EDIT_ON?}
		<a href=?c=assignement&soid={?$item->attributes.soid?} style="font-size:11px;"><img src="{?$sitepath?}images/icons/people.gif" alt="{?t?}Назначить{?/t?}" border=0 width=15></a></td>
	{?/if?}
	<td>
	{?if $smarty.const.STRUCTURE_OF_ORGAN_PERM_EDIT_ON?}	
		<a href='?c=edit&soid={?$item->attributes.soid?}'><img title='' src='{?$sitepath?}images/icons/edit.gif' border=0 hspace='3'></a>&nbsp;&nbsp;
		<a href=?c=delete&soid={?$item->attributes.soid?}
		onclick="if (!confirm('{?t?}Удалить?{?/t?}')) return false;" ><img src='{?$sitepath?}images/icons/delete.gif' border=0 hspace='3'></a>
	{?/if?}
	</td>
</tr>