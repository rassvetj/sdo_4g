<?php
class HM_Payment_PaymentTable extends HM_Db_Table
{
    protected $_name = "student_payments";
    
    protected $_primary = "payment_id";
    protected $_sequence = "";
	
    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('student_payments.payment_id ASC');
    }
}