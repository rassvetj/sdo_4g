<?php

class HM_User_Password_PasswordTable extends HM_Db_Table
{
    protected $_name = "password_history";
    protected $_primary = array('user_id', 'password', 'change_date');
    //protected $_sequence = "S_43_1_OPTIONS";

    public function getDefaultOrder()
    {
        return array('password_history.user_id ASC');
    }
}