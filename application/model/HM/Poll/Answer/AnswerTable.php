<?php

class HM_Poll_Answer_AnswerTable extends HM_Db_Table
{
    protected $_name = "quizzes_answers";
    protected $_primary = array("quiz_id", "question_id", "answer_id");
    //protected $_sequence = "S_65_1_QUIZZES";

    protected $_referenceMap = array(
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
        )
    );

    public function getDefaultOrder()
    {
        return array('quizzes_answers.question_title ASC');
    }
}