<?php

class HM_Group_GroupTable extends HM_Db_Table
{
    protected $_name = "groupname";
    protected $_primary = "gid";
    protected $_sequence = "S_31_1_GROUPNAME";

    protected $_dependentTables = array("HM_Group_Assign_AssignTable", "HM_Speciality_Group_GroupTable");

    protected $_referenceMap = array(
        'Assign' => array(
            'columns'       => 'gid',
            'refTableClass' => 'HM_Group_Assign_AssignTable',
            'refColumns'    => 'gid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'SpecialityAssign' => array(
            'columns'       => 'gid',
            'refTableClass' => 'HM_Speciality_Group_GroupTable',
            'refColumns'    => 'gid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'specialities' // имя свойства текущей модели куда будут записываться модели зависимости        
        )

    );

    public function getDefaultOrder()
    {
        return array('groupname.name ASC');
    }
}