<?php

class HM_Test_TestTable extends HM_Db_Table
{
    protected $_name = "test";
    protected $_primary = "tid";
    protected $_sequence = "S_65_1_TEST";

    protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'tid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'questions' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('test.tid ASC');
    }
}