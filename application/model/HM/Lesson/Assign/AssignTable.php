<?php

class HM_Lesson_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "scheduleID";
    protected $_primary = "SSID";
    protected $_sequence = "S_58_1_SCHEDULEID";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Lesson' => array(
            'columns'       => 'SHEID',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'SHEID',
            'propertyName'  => 'lessons' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        ),
        'MarkHistory' => array(
            'columns' => 'SSID',
            'refTableClass' => 'HM_Lesson_Assign_MarkHistory_MarkHistoryTable',
            'refColumns' => 'SSID',
            'propertyName' => 'markHistory')

    );

    public function getDefaultOrder()
    {
        return array('scheduleID.SSID ASC');
    }
}