<?php

class HM_Role_TeacherTable extends HM_Db_Table
{
    protected $_name = "Teachers";
    protected $_primary = "PID";
    protected $_sequence = "S_63_1_TEACHERS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'teachers'
        ),
        'Course' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses'
        ),
        'Subject' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'courses')

    );

    public function getDefaultOrder()
    {
        return array('Teachers.PID');
    }
}