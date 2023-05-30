<?php
class HM_Lesson_Assign_MarkHistory_MarkHistoryTable extends HM_Db_Table
{
     protected $_name = "schedule_marks_history";
     protected $_primary = array('MID','SSID','mark','updated');
     protected $_referenceMap = array(
        'LessonAssign' => array(
            'columns'       => 'SSID',
            'refTableClass' => 'HM_Lesson_Assign_AssignTable',
            'refColumns'    => 'SSID',
            'propertyName'  => 'lessonAssigns'
        ),
        'Teacher' => array(
            'columns'       => 'MID',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'teachers'
        )
    );
}