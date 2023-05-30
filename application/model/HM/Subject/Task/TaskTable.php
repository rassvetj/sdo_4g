<?php

class HM_Subject_Task_TaskTable extends HM_Db_Table
{
    protected $_name = "subjects_tasks";
    protected $_primary = array("subject_id", "task_id");

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
        'Task' => array(
            'columns' => 'task_id',
            'refTableClass' => 'HM_Task_TaskTable',
            'refColumns' => 'task_id',
            'propertyName' => 'tasks'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_tasks.subject_id ASC');
    }
}