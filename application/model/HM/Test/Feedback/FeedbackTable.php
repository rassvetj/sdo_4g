<?php

class HM_Test_Feedback_FeedbackTable extends HM_Db_Table
{
    protected $_name = "test_feedback";
    protected $_primary = "test_feedback_id";
    protected $_sequence = "S_100_TEST_FEEDBACK";

/*    protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'tid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'questions' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );*/

    public function getDefaultOrder()
    {
        return array('test_feedback.test_feedback_id ASC');
    }
}