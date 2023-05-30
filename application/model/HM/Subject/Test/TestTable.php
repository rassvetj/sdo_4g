<?php

class HM_Subject_Test_TestTable extends HM_Db_Table
{
    protected $_name = "subjects_tests";
    protected $_primary = array("subject_id", "test_id");

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
        'Test' => array(
            'columns' => 'test_id',
            'refTableClass' => 'HM_Test_Abstract_AbstractTable',
            'refColumns' => 'test_id',
            'propertyName' => 'tests'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_courses.subject_id ASC');
    }
}