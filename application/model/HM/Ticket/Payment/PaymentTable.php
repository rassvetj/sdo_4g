<?php
class HM_Ticket_Payment_PaymentTable extends HM_Db_Table
{
    protected $_name = "Ticket_order_payments";
    protected $_primary = "payment_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('Ticket_order_payments.payment_id ASC');
    }
}