<?php

class HM_Speciality_Course_CourseTable extends HM_Db_Table
{
    protected $_name = "tracks2course";
    protected $_primary = array("trid", "cid", "level");

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Speciality' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_SpecialityTable',
            'refColumns'    => 'trid',
            'propertyName'  => 'specialities' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses'
        )

    );

    public function getDefaultOrder()
    {
        return array('tracks2course.cid ASC');
    }
}