<?php

class HM_Test_Question_QuestionTable extends HM_Db_Table
{
    protected $_name = "tests_questions";
    protected $_primary = array("kod", "test_id", 'subject_id');
    //protected $_sequence = "S_65_1_TEST";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Question_QuestionTable',
            'refColumns'    => 'kod',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'question' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'TestAbstract' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_Test_Abstract_AbstractTable',
            'refColumns'    => 'test_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'test' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('tests_questions.test_id ASC');
    }
}