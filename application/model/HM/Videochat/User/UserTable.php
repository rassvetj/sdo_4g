<?php

class HM_Videochat_User_UserTable extends HM_Db_Table
{
    protected $_name = "videochat_users";
    protected $_primary = array('pointId' ,'userId');

    public function getDefaultOrder()
    {
        return array('videochat.userId');
    }
}