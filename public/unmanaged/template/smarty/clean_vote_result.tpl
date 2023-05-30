{?if $action=='clean'?}

{?else?}

<script type="text/javascript">
<!--
function reselect(mid, checked) {
	var elm;
	var i=1;
	while(elm = document.getElementById('poll_'+mid+'_'+i)) {
		elm.checked = checked;
		i++;
	}
}
//-->
</script>

<form method="POST">
<input name="action" type="hidden" value="clean">
<table width=100% cellspacing=0 class=main>
<tr><th>{?t?}Пользователи{?/t?}</th><th>{?t?}Опросы{?/t?}</th></tr>
{?if $persons?}
    {?foreach from=$persons key=mid item=person?}
    <tr>
        <td><input checked name="mids[]" type="checkbox" value="{?$mid?}" onClick="reselect({?$mid?},this.checked);">{?$person.name?}</td>
        <td>
        {?if $person.polls?}
            {?foreach name='foreach' from=$person.polls key=pid item=poll?}
            <input id="poll_{?$mid?}_{?$smarty.foreach.foreach.iteration?}" checked name="polls[{?$mid?}][]" type="checkbox" value="{?$pid?}"> {?$poll?}<br>
            {?/foreach?}
        {?/if?}
        </td>
    </tr>
    {?/foreach?}
    <tr><td colspan=2>{?$okbutton?}</td></tr>
{?else?}
<tr><td colspan=2 align=center>{?t?}нет{?/t?}</td></tr>
{?/if?}
</table>
</form>
{?/if?}