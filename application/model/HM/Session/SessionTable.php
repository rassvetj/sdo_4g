<?php

class HM_Session_SessionTable extends HM_Db_Table
{
    protected $_name = "sessions";
    protected $_primary = "sessid";
    protected $_sequence = "S_89_1_SESSIONS";

    //protected $_dependentTables = array("HM_Role_AdminTable", "HM_Role_DeanTable", "HM_Role_TeacherTable", "HM_Role_StudentTable");

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );

    public function getDefaultOrder()
    {
        return array('sessions.start ASC');
    }
}