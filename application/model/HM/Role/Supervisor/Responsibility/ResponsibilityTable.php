<?php

class HM_Role_Supervisor_Responsibility_ResponsibilityTable extends HM_Db_Table
{
    protected $_name = "supervisors_responsibilities";
    protected $_primary = "user_id";
    //protected $_sequence = 'S_45_1_PEOPLE';
    
    protected $_dependentTables = array();

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'users'
        ),
    );

}