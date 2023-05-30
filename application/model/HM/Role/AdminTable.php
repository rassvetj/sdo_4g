<?php

class HM_Role_AdminTable extends HM_Db_Table
{
    protected $_name = "admins";
    protected $_primary = "AID";
    protected $_sequence = "S_77_1_ADMINS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );

    public function getDefaultOrder()
    {
        return array('admins.AID');
    }
}