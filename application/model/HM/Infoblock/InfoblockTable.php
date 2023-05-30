<?php

class HM_Infoblock_InfoblockTable extends HM_Db_Table
{
    protected $_name = "interface";
    protected $_primary = "interface_id";
    protected $_sequence = "S_100_1_INTERFACE";

  

    public function getDefaultOrder()
    {
        return array('interface.x , interface.y');
    }
}