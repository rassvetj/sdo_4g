<?php

class HM_Course_Rubric_RubricTable extends HM_Db_Table
{
    protected $_name = "courses_groups";
    protected $_primary = "did";
    protected $_sequence = "S_117_1_COURSES_GROUPS";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    /*
    protected $_referenceMap = array(
        'Student' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'CID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'students' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );
    */

    public function getDefaultOrder()
    {
        return array('courses_groups.name ASC');
    }
}