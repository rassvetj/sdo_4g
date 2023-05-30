<?php

class HM_Webinar_History_Current_CurrentTable extends HM_Db_Table
{
    protected $_name = "webinar_plan_current";
    protected $_primary = 'pointId';

    public function getDefaultOrder()
    {
        return array('webinar_plan_current.pointId');
    }
}