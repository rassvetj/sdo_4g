<?php

class HM_Role_Custom_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "permission2mid";
    protected $_primary = array("pmid", "mid");

    //protected $_dependentTables = array("HM_Role_AdminTable", "HM_Role_DeanTable", "HM_Role_TeacherTable", "HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Role' => array(
            'columns'       => 'pmid',
            'refTableClass' => 'HM_Role_Custom_CustomTable',
            'refColumns'    => 'pmid',
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );

    public function getDefaultOrder()
    {
        return array('permission2mid.mid ASC');
    }
}