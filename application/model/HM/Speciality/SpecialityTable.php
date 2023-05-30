<?php

class HM_Speciality_SpecialityTable extends HM_Db_Table
{
    protected $_name = "tracks";
    protected $_primary = "trid";
    protected $_sequence = "S_70_1_TRACKS";

    protected $_dependentTables = array("HM_Speciality_Assign_AssignTable", "HM_Speciality_Course_CourseTable", "HM_Speciality_Group_GroupTable");

    protected $_referenceMap = array(
        'Assign' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_Assign_AssignTable',
            'refColumns'    => 'trid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'CourseAssign' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_Course_CourseTable',
            'refColumns'    => 'trid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'courses' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'GroupAssign' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_Group_GroupTable',
            'refColumns'    => 'trid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'groups' // имя свойства текущей модели куда будут записываться модели зависимости        
        )

    );

    public function getDefaultOrder()
    {
        return array('tracks.name ASC');
    }
}