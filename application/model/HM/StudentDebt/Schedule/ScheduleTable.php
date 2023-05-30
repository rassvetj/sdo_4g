<?php
class HM_StudentDebt_Schedule_ScheduleTable extends HM_Db_Table
{
    protected $_name = "student_debts_schedule";
    protected $_primary = "schedule_id";


    public function getDefaultOrder()
    {
        return array('student_debts_schedule.schedule_id ASC');
    }
}