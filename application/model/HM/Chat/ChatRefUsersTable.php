<?php

class HM_Chat_ChatRefUsersTable extends HM_Db_Table
{
    protected $_name = "chat_ref_users";
    protected $_primary = array('channel_id', 'user_id');
    
    protected $_referenceMap = array(
        'ChatChannels' => array(
            'columns'       => 'channel_id',
            'refTableClass' => 'HM_Chat_ChatChannelsTable',
            'refColumns'    => 'id',
            'propertyName'  => 'chatchannels'
        ),
        'ChatHistory' => array(
            'columns'       => 'channel_id',
            'refTableClass' => 'HM_Chat_ChatHistoryTable',
            'refColumns'    => 'channel_id',
            'propertyName'  => 'chatHistory'
        ),
        'Users' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Recipient' => array ( // то же самое, что предыдущее, только с однозначным названием
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'recipient'
        )
    );
    
    public function getDefaultOrder()
    {
        return array();
    }
}