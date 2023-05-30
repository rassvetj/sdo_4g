<script type="text/javascript" language="JavaScript">
<!--
function getUsers() {
    ajaxCallFunction('chatGetUsers', new Array(), function(msg) {var data = msg.substring(2); eval(data); var elm = jQuery('#users'); elm.html(res);});
    if (typeof timerUsers != 'undefined') {
        clearTimeout(timerUsers);
    }    
    timerUsers = setTimeout('getUsers()', 5000);            
}
//-->
</script>
<div style="padding: 10px">
	<div id="users">
	   {?include file="ajaxchat_users_list.tpl"?}
	</div>
</div>

<script type="text/javascript" language="JavaScript">
<!--
getUsers();
//-->
</script>