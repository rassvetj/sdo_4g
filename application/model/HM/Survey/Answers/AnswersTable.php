<?php

class HM_Survey_Answers_AnswersTable extends HM_Db_Table
{
    protected $_name = "survey_answers";
    protected $_primary = 'answer_id';
    
    public function getDefaultOrder()
    {
        return array('survey_answers.answer_id ASC');
    }
}