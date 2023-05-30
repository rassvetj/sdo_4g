<?php

class HM_Group_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "groupuser";
    protected $_primary = array("gid", "mid");

    //protected $_dependentTables = array("HM_Role_AdminTable", "HM_Role_DeanTable", "HM_Role_TeacherTable", "HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Group' => array(
            'columns'       => 'gid',
            'refTableClass' => 'HM_Group_GroupTable',
            'refColumns'    => 'gid',
            'propertyName'  => 'groups' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        )

    );

    public function getDefaultOrder()
    {
        return array('groupuser.gid ASC');
    }
}