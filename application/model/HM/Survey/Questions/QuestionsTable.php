<?php

class HM_Survey_Questions_QuestionsTable extends HM_Db_Table
{
    protected $_name = "survey_questions";
    protected $_primary = 'question_id';
    
    public function getDefaultOrder()
    {
        return array('survey_questions.question_id ASC');
    }
}