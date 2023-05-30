<?php

class HM_Lesson_LessonTable extends HM_Db_Table
{
    protected $_name = "schedule";
    protected $_primary = "SHEID";
    protected $_sequence = "S_57_1_SCHEDULE";

    protected $_dependentTables = array("HM_Lesson_Assign_AssignTable", 'HM_User_UserTable', 'HM_Event_EventTable');

    protected $_referenceMap = array(        
        'Subject' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject'          
        ),
        'Assign' => array(
            'columns'       => 'SHEID',
            'refTableClass' => 'HM_Lesson_Assign_AssignTable',
            'refColumns'    => 'SHEID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'assigns'
        ),
        'Teacher' => array(
            'columns'       => 'teacher',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'teacher'
        ),
        'Statistic' => array(
            'columns'       => 'SHEID',
            'refTableClass' => 'HM_Module_Test_Statistic_StatisticTable',
            'refColumns'    => 'sheid',
            'propertyName'  => 'statistics'
        ),
        'Event' => array(
            'columns'       => 'typeID',
            'refTableClass' => 'HM_Event_EventTable',
            'refColumns'    => 'event_id',
            'propertyName'  => 'event'
        ),
        'Section' => array(
            'columns'       => 'section_id',
            'refTableClass' => 'HM_Section_SectionTable',
            'refColumns'    => 'section_id',
            'propertyName'  => 'section'
        ),
    );

    public function getDefaultOrder()
    {
        /**
         * По умолчанию сортировка по порядку.
         * @author Artem Smirnov <tonakai.personal@gmail.com>
         * @date 28 december 2012
         */
        return array('schedule.order ASC','schedule.title ASC');
    }
}