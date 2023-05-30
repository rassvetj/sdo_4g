<?php

class HM_Role_TutorTable extends HM_Db_Table
{
    protected $_name = "Tutors";
    protected $_primary = "TID";
    protected $_sequence = "S_63_1_TUTORS";
    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'tutors'
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
        return array('Tutors.TID');
    }
}