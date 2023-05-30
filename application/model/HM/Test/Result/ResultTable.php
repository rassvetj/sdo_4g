<?php

class HM_Test_Result_ResultTable extends HM_Db_Table
{
    protected $_name = "loguser";
    protected $_primary = "stid";
    protected $_sequence = "S_36_1_LOGUSER";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Statistic' => array(
            'columns'       => 'stid',
            'refTableClass' => 'HM_Question_Result_ResultTable',
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
            'propertyName'  => 'tests'
        ),
        'Lesson' => array(
            'columns'       => 'sheid',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons'            
        )

    );

    public function getDefaultOrder()
    {
        return array('loguser.stid ASC');
    }
}