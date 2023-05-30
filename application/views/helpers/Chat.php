<?php

class HM_View_Helper_Chat extends HM_View_Helper_Abstract
{
    public function chat($name, $otions = null, $params = null, $attribs = null)
    {   
        $config = Zend_Registry::get('config');
        $this->view->headScript()->appendFile($config->url->base.'js/hmchat.js');
        
        $channel = $otions['channel'];
        $curUser = Zend_Registry::get('serviceContainer')->getService('User')->getCurrentUser();
        $curUserId = $curUser->MID;
        
        $js = "$.hmchat = new HMChat('".$config->chat->url."', ".$channel->id.", '".$curUser->generateKey()."');";
        $js .= "$.hmchat.onMessageAdd = function(html) {
            if(html == null) {
                return;
            }
            $('#chat-list').prepend(html);
            $('#chat-list li').removeClass('even').removeClass('odd');
            $('#chat-list li:even').addClass('even');
            $('#chat-list li:odd').addClass('odd');
        };\n";
        $js .= "$.hmchat.buildMessage = function(message) {
            if($('#msg_'+message.id).size() > 0) {
                return null;
            }
            var loginClass = (message.sender_id == $curUserId) ? 'current ' : '';
            var html = '<li id=\"msg_'+message.id+'\" class=\"message\">'+
                       '    <span class=\"date\">'+message.created+'</span><br>'+
                       '    <a href=\"javascript: void(null);\" rel=\"'+message.sender_id+'\" class=\"'+loginClass+'login\">'+message.sender_login+'</a><span class=\"pointer\">></span>'+message.message+
                       '</li>';
            return html;
        };\n";
        $js .= "setTimeout(function() {
           $.hmchat.listen(0);
        }, 500);";
        
        $js .= "$('#send').click(function(){
            var message = $.trim($('#msg-field').val());
            if(message.length == 0) {
                return ;
            }
            $.ajax({
                url: '".$this->view->url(array(
                        'module' => 'chat',
                        'controller' => 'index',
                        'action' => 'send',
                        'channel_id' => $channel->id, 
                        'subject' => $this->view->subjectName, 
                        'subject_id' => $this->view->subjectId
                    ), null, true)."',
                type: 'POST',
                dataType: 'JSON',
                data: {
                    message: message,
                    receiver: $('#receiver-field').val(),
                },
                success: function(r){
                    $('#msg-field').val('');
                    $('#receiver-field').val('');
                    // console.log(r);
                },
                error: function(xhr, textStatus, errorThrown) {
                    if(errorThrown != undefined) {
                        alert(textStatus + ' | ' + errorThrown);
                    }
                }
            });
        });
        $('#msg-field').keypress(function(evt){
            if(evt.keyCode == 13) {
                setTimeout(function(){
                    $('#msg-field').blur();
                    $('#send').click();
                    $('#msg-field').focus();
                }, 100);
                return false;
            }
        });
        $(document).on('click', '.login', function(){
            var id = $(this).attr('rel');
            $('#receiver-field').val(id);
            $('#msg-field').val($.trim($(this).text())+', ');
        });";
        
        $this->view->jQuery()->addOnload($js);
        $html = '<input type="hidden" id="receiver-field"/>
            <input type="text" id="msg-field"/>
            <input type="button" value="'. _('Ok') .'" id="send"/>';
        return $html;
    }
}