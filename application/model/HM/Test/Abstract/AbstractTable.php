<?php

class HM_Test_Abstract_AbstractTable extends HM_Db_Table
{
    protected $_name = "test_abstract";
    protected $_primary = "test_id";
    protected $_sequence = "S_65_1_TEST_ABSTRACT";

    //protected $_dependentTables = array("HM_Test_Question_QuestionTable");

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'test_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'testQuestions'
        ),
        'SubjectAssign' => array(
            'columns'       => 'test_id',
            'refTableClass' => 'HM_Subject_Test_TestTable',
            'refColumns'    => 'test_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'subjects' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('test_abstract.test_id ASC');
    }
}