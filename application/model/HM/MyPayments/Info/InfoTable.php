<?php
class HM_MyPayments_Info_InfoTable extends HM_Db_Table
{
    protected $_name 			= 'payments_info';
    protected $_primary 		= 'payment_id';
    protected $_sequence 		= '';

    protected $_referenceMap 	= array();

    public function getDefaultOrder()
    {
        return array('payments_info.payment_id ASC');
    }
}