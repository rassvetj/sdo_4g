<?php

class HM_Role_ManagerTable extends HM_Db_Table
{
    protected $_name = "managers";
    protected $_primary = "id";
    protected $_sequence = "S_114_1_MANAGERS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );

    public function getDefaultOrder()
    {
        return array('managers.mid');
    }
}