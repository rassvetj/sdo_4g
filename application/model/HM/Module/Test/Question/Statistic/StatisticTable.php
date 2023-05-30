<?php

class HM_Module_Test_Question_Statistic_StatisticTable extends HM_Db_Table
{
    protected $_name = "logseance";
    //protected $_primary = "stid";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Statistic' => array(
            'columns'       => 'stid',
            'refTableClass' => 'HM_Module_Test_Statistic_StatisticTable',
            'refColumns'    => 'stid',
            'propertyName'  => 'statistics' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Course' => array(
            'columns'       => 'cid',
            'refTableClass' => 'HM_Course_CourseTable',
            'refColumns'    => 'CID',
            'propertyName'  => 'courses' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Test' => array(
            'columns'       => 'tid',
            'refTableClass' => 'HM_Module_Test_TestTable',
            'refColumns'    => 'tid',
            'propertyName'  => 'tests' // eiy naienoaa oaeouae iiaaee eoaa aoaoo caienuaaouny iiaaee caaeneiinoe
        ),
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Module_Test_Question_QuestionTable',
            'refColumns'    => 'kod',
            'propertyName'  => 'questions' // eiy naienoaa oaeouae iiaaee eoaa aoaoo caienuaaouny iiaaee caaeneiinoe       
        )

    );

    public function getDefaultOrder()
    {
        return array('logseance.stid ASC');
    }
}