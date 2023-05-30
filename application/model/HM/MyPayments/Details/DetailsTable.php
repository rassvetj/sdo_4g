<?php
class HM_MyPayments_Details_DetailsTable extends HM_Db_Table
{
    protected $_name 			= 'payment_details';
    protected $_primary 		= 'detail_id';
    protected $_sequence 		= '';

    protected $_referenceMap 	= array();

    public function getDefaultOrder()
    {
        return array('payment_details.detail_id ASC');
    }
}