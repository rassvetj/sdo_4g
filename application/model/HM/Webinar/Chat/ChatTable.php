<?php

class HM_Webinar_Chat_ChatTable extends HM_Db_Table
{
    protected $_name = "webinar_chat";
    protected $_primary = 'id';
    protected $_sequence = 'S_107_1_WEBINAR_CHAT';

    public function getDefaultOrder()
    {
        return array('webinar_chat.id');
    }
}