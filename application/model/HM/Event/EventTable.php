<?php

class HM_Event_EventTable extends HM_Db_Table
{
    protected $_name = "events";
    protected $_primary = "event_id";
    protected $_sequence = "S_23_1_EVENTS";

    protected $_dependentTables = array(
        "HM_Event_Weight_WeightTable"
    );

    protected $_referenceMap = array(
        'Lesson' => array(
            'columns'       => 'event_id',
            'refTableClass' => 'HM_Lesson_LessonTable',
            'refColumns'    => 'typeID', // не совсем так
            'propertyName'  => 'lessons' 
        ),
        'Weight' => array(
            'columns'       => 'event_id',
            'refTableClass' => 'HM_Event_Weight_WeightTable',
            'refColumns'    => 'event',
            'propertyName'  => 'events' 
        )
    );

    public function getDefaultOrder()
    {
        return array('events.title ASC');
    }
}