
<script type="text/javascript">
<!--
{?$javascript?}
//-->
</script>

<table border=0>
	<tr>
		<td valign=top>
			{?$list1_title?}<br>
			<input type="button" value="{?t?}Все{?/t?}" style="width: 10%" onClick="{?$button_all_click?}">
			<input type="text" name="{?$editbox_search_name?}" id="{?$editbox_search_name?}" value="{?$editbox_search_text?}" style="width: 88%" onKeyUp="{?$editbox_search_keyup?}">
			<div id="{?$list1_container_id?}">
			<select id="{?$list1_name?}" name="{?$list1_name?}" style="width: 300px">
			{?$list1_options?}
			</select>
			</div>
            {?if $list3_options?}
                <br>
                <select id="{?$list3_name?}" name="{?$list3_name?}" style="width: 300px" onChange="{?$list3_change?}">
                {?$list3_options?}
                </select>
            {?/if?}
		</td>
	</tr>
</table>
