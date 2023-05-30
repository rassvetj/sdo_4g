<?php

class HM_User_Info_InfoTable extends HM_Db_Table
{
    protected $_name = "People_info";
    protected $_primary = 'info_id';
    
    public function getDefaultOrder()
    {
        return array('People_info.info_id ASC');
    }
}