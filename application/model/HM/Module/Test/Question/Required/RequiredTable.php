<?php

class HM_Module_Test_Question_Required_RequiredTable extends HM_Db_Table
{
    protected $_name = "testneed";
    protected $_primary = array("tid", "kod");

    //protected $_dependentTables = array("HM_Role_StudentTable");
    
    protected $_referenceMap = array(
        'Test' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Modle_Test_TestTable',
            'refColumns'    => 'tid',
            'propertyName'  => 'test' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Modle_Test_Question_QuestionTable',
            'refColumns'    => 'kod',
            'propertyName'  => 'question' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('testneed.kod ASC');
    }
}