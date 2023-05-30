<?php

class HM_Task_TaskTable extends HM_Db_Table
{
    protected $_name = "tasks";
    protected $_primary = "task_id";
    protected $_sequence = "S_102_1_TASKS";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'SubjectAssign' => array(
            'columns'       => 'task_id',
            'refTableClass' => 'HM_Subject_Task_TaskTable',
            'refColumns'    => 'task_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'AuthorAssign' => array(
            'columns'       => 'created_by',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'author' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('quizzes.quiz_id ASC');
    }
}