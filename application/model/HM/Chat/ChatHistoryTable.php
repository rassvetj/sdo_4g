<?php

class HM_Chat_ChatHistoryTable extends HM_Db_Table
{
    protected $_name = "chat_history";
    protected $_primary = 'id';
    protected $_sequence = "S_ID_CHAT_HISTORY";

    protected $_referenceMap = array(
        'Users' => array(
            'columns'       => 'sender',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'Sender' => array( // то же самое, что предыдущее, только с однозначным названием
            'columns'       => 'sender',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'sender'
        ),
        'ChatRefUser' => array(
            'columns'       => 'channel_id',
            'refTableClass' => 'HM_Chat_ChatRefUsersTable',
            'refColumns'    => 'channel_id',
            'propertyName'  => 'chatRefUsers'
        )
    );
    
    public function getDefaultOrder()
    {
        return array();
    }
}