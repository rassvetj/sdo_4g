<?php

class HM_Question_Result_ResultTable extends HM_Db_Table
{
    protected $_name = "logseance";
    //protected $_primary = array("stid", "kod");
    //protected $_sequence = "S_36_1_LOGUSER";


    protected $_referenceMap = array(
        'Statistic' => array(
            'columns'       => 'stid',
            'refTableClass' => 'HM_Test_Result_ResultTable',
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
            'refTableClass' => 'HM_Test_TestTable',
            'refColumns'    => 'tid',
            'propertyName'  => 'tests' // eiy naienoaa oaeouae iiaaee eoaa aoaoo caienuaaouny iiaaee caaeneiinoe
        ),
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Question_QuestionTable',
            'refColumns'    => 'kod',
            'propertyName'  => 'questions' // eiy naienoaa oaeouae iiaaee eoaa aoaoo caienuaaouny iiaaee caaeneiinoe       
        )

    );

    public function getDefaultOrder()
    {
        return array('logseance.stid ASC');
    }
}