<?php

class HM_Timetable_Additional_AdditionalTable extends HM_Db_Table
{
    protected $_name = 'timetable_additional';
    protected $_primary = array('additional_id');


    public function getDefaultOrder()
    {
        return array('timetable_additional.additional_id ASC');
    }
}