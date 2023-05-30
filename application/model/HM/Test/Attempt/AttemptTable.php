<?php

class HM_Test_Attempt_AttemptTable extends HM_Db_Table
{
    protected $_name = "testcount";
    protected $_primary = array("mid", "tid", 'cid', 'lesson_id');
    //protected $_sequence = "S_65_1_TEST";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");
/*
    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'tid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'questions' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );
*/
    public function getDefaultOrder()
    {
        return array('tescount.tid ASC');
    }
}