<?php

class HM_User_Info_Confirm_ConfirmTable extends HM_Db_Table
{
    protected $_name 	= 'People_info_confirm';
    protected $_primary = array('confirm_id');
    
    public function getDefaultOrder()
    {
        return array('People_info_confirm.confirm_id ASC');
    }
}