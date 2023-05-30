<?php

class HM_Role_EmployeeTable extends HM_Db_Table
{
    protected $_name = "employee";
    protected $_primary = "user_id";
    //protected $_sequence = "S_77_1_ADMINS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        )
    );

    public function getDefaultOrder()
    {
        return array('employee.user_id');
    }
}