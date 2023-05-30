<?php

class HM_Poll_Result_ResultTable extends HM_Db_Table
{
    protected $_name = "quizzes_results";
    protected $_primary = array("user_id", "lesson_id", "question_id", "answer_id");
    //protected $_sequence = "S_65_1_QUIZZES";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
        ),
        'Quizz' => array(
            'columns'       => 'quiz_id',
            'refTableClass' => 'HM_Poll_PollTable',
            'refColumns'    => 'quiz_id',
            'propertyName'  => 'quizzes'
        ),
        'Question' => array(
        	'columns'		=> 'answer_id',
        	'refTableClass' => 'HM_Question_QuestionTable',
        	'refColumns'	=> 'kod',
        	'propertyName'	=> 'questions'
        ),        
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        )
    );

    public function getDefaultOrder()
    {
        return array('quizzes.quiz_id ASC');
    }
}