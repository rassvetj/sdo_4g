<?php

class HM_Poll_PollTable extends HM_Db_Table
{
    protected $_name = "quizzes";
    protected $_primary = "quiz_id";
    protected $_sequence = "S_65_1_QUIZZES";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'SubjectAssign' => array(
            'columns'       => 'quiz_id',
            'refTableClass' => 'HM_Subject_Poll_PollTable',
            'refColumns'    => 'quiz_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('quizzes.quiz_id ASC');
    }
}