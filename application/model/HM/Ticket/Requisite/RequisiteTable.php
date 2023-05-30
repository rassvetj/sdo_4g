<?php
class HM_Ticket_Requisite_RequisiteTable extends HM_Db_Table
{
    protected $_name = "ticket_requisites";
    protected $_primary = "requisite_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('ticket_requisites.requisite_id ASC');
    }
}