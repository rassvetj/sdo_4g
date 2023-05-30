<?php
class HM_Timetable_TimetableTable extends HM_Db_Table
{
    protected $_name 		= 'timetable';    
    protected $_primary 	= 'timetable_id';
    protected $_sequence 	= '';

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('timetable.timetable_id ASC');
    }
}