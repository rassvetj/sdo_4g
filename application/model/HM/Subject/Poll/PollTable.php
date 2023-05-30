<?php

class HM_Subject_Poll_PollTable extends HM_Db_Table
{
    protected $_name = "subjects_quizzes";
    protected $_primary = array("subject_id", "quiz_id");

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
        'Poll' => array(
            'columns' => 'quiz_id',
            'refTableClass' => 'HM_Poll_PollTable',
            'refColumns' => 'quiz_id',
            'propertyName' => 'polls'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_courses.subject_id ASC');
    }
}