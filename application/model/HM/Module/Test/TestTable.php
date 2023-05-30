<?php

class HM_Module_Test_TestTable extends HM_Db_Table
{
    protected $_name = "test";
    protected $_primary = "tid";
    protected $_sequence = "S_65_1_TEST";

    protected $_dependentTables = array("HM_Module_Test_Question_Required_RequiredTable");

    protected $_referenceMap = array(
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'course' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Owner' => array(
            'columns'       => 'cidowner',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'owner'
        ),
        'Required' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Module_Test_Question_Required_RequiredTable',
            'refColumns'    => 'tid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'required'
        ),
        'Statistic' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Module_Test_Statistic_StatisticTable',
            'refColumns'    => 'tid',
            'propertyName'  => 'statistics'
        )
    );

    public function getDefaultOrder()
    {
        return array('test.title ASC');
    }
}