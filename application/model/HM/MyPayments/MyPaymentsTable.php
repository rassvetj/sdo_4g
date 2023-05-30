<?php
class HM_MyPayments_MyPaymentsTable extends HM_Db_Table
{
    protected $_name 			= "payments";
    
    protected $_primary  		= "payment_id";
    protected $_sequence 		= "";
	
    protected $_referenceMap 	= array(
    );

    public function getDefaultOrder()
    {
        return array('payments.payment_id ASC');
    }
}