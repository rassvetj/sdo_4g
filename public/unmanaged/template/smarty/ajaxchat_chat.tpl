<script type="text/javascript" language="JavaScript">
<!--

function ajaxCallFunction(name, params) {

	var post_data = "rs=" + escape(name);
	post_data += "&rst=" + escape();
	post_data += "&rsrnd=" + new Date().getTime();
	for (var i = 0; i < params.length; i++) {
		post_data = post_data + "&rsargs[]=" + params[i];
	}

	var successFunction = function(msg) {};

	if (ajaxCallFunction.arguments.length >= 3) {
		successFunction = ajaxCallFunction.arguments[2];
	}

	var ret = jQuery.ajax({
		type: "POST",
		url: "{?$this->root_url?}/ajax_listener.php",
		data: post_data,
		success: successFunction
	});

}

function sendMessage() {
    jQuery('#sending').get(0).style.display = 'block';
    var messageElm = jQuery('#message').get(0);
    if (messageElm && messageElm.value.length) {    
        messageElm.disabled = true;
        ajaxCallFunction('chatSendMessage', new Array(messageElm.value), function(msg) {messageElm.value = ''; getMessages();});
    } 
    
    messageElm.disabled = false;
    messageElm.focus();
    jQuery('#sending').get(0).style.display = 'none';
}

function addNick(nickname) {
    var elm = jQuery('#message').get(0);
    if (elm) {
        elm.value += nickname+', ';
        elm.focus();
    }
}

function getMessages() {
    ajaxCallFunction('chatGetMessages', new Array(), function(msg) {var data = msg.substring(2); eval(data); var elm = jQuery('#messages'); elm.html(res); elm.get(0).scrollTop = elm.get(0).scrollHeight;});
    if (typeof timer != 'undefined') {
        clearTimeout(timer);
    }    
    timer = setTimeout('getMessages()', 5000);
}

function getClientHeight() {
    return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientHeight:document.body.clientHeight;
}

function getClientWidth() {
  return document.compatMode=='CSS1Compat' && !window.opera?document.documentElement.clientWidth:document.body.clientWidth;
}

function ajustMessagesSize() {
    /*$('#messages').css({
        width: $('div #ui-tabs-2').width() - 60,
        height: $('div #ui-tabs-2').height() - 79
    });*/
    //var clientHeight = getClientHeight() -;
    //var clientWidth = getClientWidth();
    //var elm = jQuery('#messages').get(0);

    //if (elm) {
    //    elm.style.height = Number(clientHeight - 60)+'px';
    //    elm.style.width  = Number(clientWidth - 40)+'px';
    //}
    //ajusttimer = setTimeout('ajustMessagesSize()', 1000);
}
//-->
</script>
<div style="padding: 10px; zoom: 1;" id="ajaxchat">
    <div id="input">
        <table border=0 cellpadding=0 cellspacing=0>
            <tr>
                <td valign=top>
                    <input onKeyDown="var e = event ? event : null; if (e && (e.keyCode == 13)) sendMessage();" type="text" style="width: 327px; font-size: 1.6em" name="message" id="message" value="">
                </td>
                <td>&nbsp;</td>
                <td valign=top>{?$okbutton?}</td>
                <td valign=middle><img id="sending" src="{?$sitepath?}images/treeview/load.gif" style="display: none"></td>
            </tr>
        </table>
    </div>
    <div id="messages" >
    {?if $messages?}
        {?foreach from=$messages item=message?}
        <span class="date">{?$message->attributes.posted|date_format:"%d.%m.%Y %H:%M:%S"?}</span>, <a onClick="addNick('{?$message->attributes.user|escape?}')" href="javascript:void(0);">{?$message->attributes.user?}</a>: {?$message->attributes.message|escape?}<br>
        {?/foreach?}
    {?/if?}
    </div>
</div>
<style type="text/css">
    body.ajaxchat {
    }
</style>
<script type="text/javascript" language="JavaScript">
<!--
ajustMessagesSize();
getMessages();
//-->
</script>