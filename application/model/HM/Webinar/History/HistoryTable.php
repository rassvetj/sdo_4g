<?php

class HM_Webinar_History_HistoryTable extends HM_Db_Table
{
    protected $_name = "webinar_history";
    protected $_primary = 'id';
    protected $_sequence = 'S_108_1_WEBINAR_HISTORY';
    
    public function getDefaultOrder()
    {
        return array('webinar_history.id');
    }
}