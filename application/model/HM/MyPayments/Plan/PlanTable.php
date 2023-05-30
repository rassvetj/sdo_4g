<?php
class HM_MyPayments_Plan_PlanTable extends HM_Db_Table
{
    protected $_name 			= 'payments_plan';
    protected $_primary 		= 'payment_id';
    protected $_sequence 		= '';

    protected $_referenceMap 	= array();

    public function getDefaultOrder()
    {
        return array('payments_plan.payment_id ASC');
    }
}