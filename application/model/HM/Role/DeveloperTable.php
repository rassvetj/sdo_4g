<?php

class HM_Role_DeveloperTable extends HM_Db_Table
{
    protected $_name = "developers";
    protected $_primary = array("mid", "cid");

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );

    public function getDefaultOrder()
    {
        return array('developers.mid');
    }
}