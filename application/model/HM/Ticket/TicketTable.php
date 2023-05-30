<?php
class HM_Ticket_TicketTable extends HM_Db_Table
{
    protected $_name = "Ticket";
    protected $_primary = "ticket_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('Ticket.ticket_id ASC');
    }
}