<?php

class HM_Dormitory_Refund_RefundTable extends HM_Db_Table
{
    protected $_name 	= "dormitory_refunds";
    protected $_primary = "refund_id";
    

    public function getDefaultOrder()
    {
        return array('dormitory_refunds.refund_id ASC');
    }
}