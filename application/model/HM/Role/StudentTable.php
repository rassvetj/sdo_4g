<?php

class HM_Role_StudentTable extends HM_Db_Table
{
    protected $_name = "Students";
    protected $_primary = "SID";
    protected $_sequence = "S_62_1_STUDENTS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
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
            'propertyName'  => 'courses'
        )
        
    );

    public function getDefaultOrder()
    {
        return array('Students.SID');
    }
}