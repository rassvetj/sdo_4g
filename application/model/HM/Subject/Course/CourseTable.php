<?php

class HM_Subject_Course_CourseTable extends HM_Db_Table
{
    protected $_name = "subjects_courses";
    protected $_primary = array("subject_id", "course_id");

/*
     protected $_dependentTables = array(
        "HM_Role_StudentTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        ),
        'Course' => array(
            'columns' => 'course_id',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns' => 'CID',
            'propertyName' => 'courses'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_courses.subject_id ASC');
    }
}