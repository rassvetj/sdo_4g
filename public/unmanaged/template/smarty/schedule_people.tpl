
<script type="text/javascript">
<!--
var globalMessage = '';
function show_room_condition_message(message) {
	globalMessage = message;
    if (message!='') {
    	alert(message);
    }

}

function show_room_condition_confirm() {
	if (globalMessage!='') {
        if (confirm(globalMessage)) return true;
        return false;
	}
	return true;
}

function get_room_condition_message() {
	var elm, mids = new Array(), sheid=0;
	if (elm = document.getElementById('sheid')) {
		sheid = elm.value;
	}

	if (elm = document.getElementById('list2')) {
	    for(i=0;i<elm.length;i++) {
	    	if (elm.options[i].value) {
                mids.push(elm.options[i].value);
            }
	    }
	}

    x_get_room_condition_message(sheid, mids.join('#'), show_room_condition_message);
}
//-->
</script>

<form action="{?$sitepath?}schedule.php4" method="POST" onSubmit="select_list_select_all('list1'); select_list_select_all('list2'); return show_room_condition_confirm();">
<input type="hidden" name="c" value="modify_people_submit">
<input type="hidden" id="sheid" name="sheid" value="{?$sheid?}">
<table width=100% class=main cellspacing=0>
<tr>
    <td colspan=2>
        {?include file="control_list2list.tpl"?}
   </td>
</tr>
<tr>
    <td colspan=2>
    {?$okbutton?}
    </td>
</tr>
</table>
</form>

<script type="text/javascript">
<!--
get_room_condition_message();
//-->
</script>