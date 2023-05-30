<?php

class HM_View_Helper_ChatSideBar extends HM_View_Helper_Abstract
{
    public function chatSideBar($name, $otions = null, $params = null, $attribs = null)
    {
        $this->view->headScript()->appendFile($this->view->baseUrl('js/lib/jquery/jquery-ui.lightdialog.js'));
    
        $js = "setInterval(function(){
                    $.getJSON('". $this->view->url(array(
                        'module' => 'chat',
                        'controller' => 'index',
                        'action' => 'channel-stat',
                        'subject_id' => $this->view->subjectId,
                        'subject' => $this->view->subjectName,
                        'channel_id' => $this->view->channel->id
                    ), null, true)."', function(res){
                        /*users*/
                        var userIds = [];
                        for(var i=0; i<res.users.length; i++) {
                            userIds.push(res.users[i].id);
                        }
                        for(var i=0; i<res.users.length; i++) {
                            var usr = res.users[i];
                            if($('#us_'+usr.id).size() == 0) {
                                var html = '<li id=\"us_'+usr.id+'\" class=\"user\">'+
                                           '    <a target=\"lightbox\" class=\"lightbox card-link\" href=\"".$this->view->url(array(
                                                'module' => 'user',
                                                'controller' => 'edit',
                                                'action' => 'card'
                                            ), null, true)."/user_id/'+usr.id+'\">'+
                                           '        <img align=\"left\" src=\"/images/content-modules/grid/card.gif\"/>'+
                                           '    </a>'+
                                           '    <a class=\"login\" href=\"javascript: void(null);\">'+usr.login+'</a><br/>'+
                                           '    <span class=\"name\">'+usr.name+'</span>'+
                                           '</li>';
                                $('.users-list .users').prepend(html);
                            }
                        }
                        $('.users-list .users li').each(function(){
                            var el = $(this);
                            var id = el.attr('id').slice(3);
                            if($.inArray(id, userIds) == -1) {
                                el.fadeOut(function(){
                                    el.remove();
                                });
                            }
                        });
                        /*channels*/
                        $('#channels-list').empty();
                        for(var i=0; i<res.channels.length; i++) {
                            var channel = res.channels[i];
                            var cssClass = 'channel';
                            if(channel.id == $('#curChannel').val()) {
                                cssClass += ' current';
                            }
                            if(channel.usersCount == 0) {
                                channel.usersCount = '';
                            }
                            var html = '<a class=\"'+cssClass+'\" href=\"". $this->view->url(array(
                                'module' => 'chat',
                                'controller' => 'index',
                                'action' => 'index',
                                'subject' => $this->view->subjectName, 
                                'subject_id' => $this->view->subjectId
                            ), null, true) . "/channel_id/'+channel.id+'\">'+channel.name+'</a> ('+channel.usersCount+')<br/>';
                            $('#channels-list').append(html);
                        }
                        /*archive*/
                        if(res.archive.length > 0) {
                            $('#channels-archive-container').show();
                            $('#channels-archive').empty();
                            for(var i=0; i<res.archive.length; i++) {
                                var channel = res.archive[i];
                                var date = channel.start_date;
                                if(channel.start_time != null && channel.end_time != null) {
                                    date += ' "._('c')." '+channel.start_time+' "._('по')." '+channel.end_time;
                                }
                                var html = '<span class=\"channel-date\">'+date+'</span> '+
                                '<a class=\"channel\" href=\"". $this->view->url(array(
                                    'module' => 'chat',
                                    'controller' => 'index',
                                    'action' => 'view',
                                    'subject' => $this->view->subjectName, 
                                    'subject_id' => $this->view->subjectId
                                ), null, true) . "/channel_id/'+channel.id+'\">'+channel.name+'</a><br/>';
                                $('#channels-archive').append(html);
                            }
                        } else {
                            $('#channels-archive-container').hide();
                        }
                    });
                }, 60000);
                $(document).on('click', '.chat-side-bar .card-link', function (event) {
                    $(event.currentTarget).lightdialog({
                        title: '"._('Карточка')."',
                        dialogClass: 'card'
                    }).lightdialog('open');
                    event.preventDefault();
                    event.stopImmediatePropagation();
                });";
        
        $this->view->jQuery()->addOnload($js);
        return '';
    }
}