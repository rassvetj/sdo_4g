<?php
class HM_Ticket_Cost_CostTable extends HM_Db_Table
{
    protected $_name = "Ticket_order_cost";
    protected $_primary = "order_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('Ticket_order_cost.order_id ASC');
    }
}