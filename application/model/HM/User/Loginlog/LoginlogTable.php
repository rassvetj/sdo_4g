<?php

class HM_User_Loginlog_LoginlogTable extends HM_Db_Table
{
    protected $_name = "user_login_log";
    protected $_primary = array('login', 'date');
    //protected $_sequence = 'S_45_1_PEOPLE';
    

    public function getDefaultOrder()
    {
        return array('user_login_log.login ASC');
    }
}