<?php

class HM_Webinar_WebinarTable extends HM_Db_Table
{
    protected $_name = "webinars";
    protected $_primary = 'webinar_id';
    protected $_sequence = 'S_100_1_WEBINARS';

    public function getDefaultOrder()
    {
        return array('webinars.webinar_id');
    }
}