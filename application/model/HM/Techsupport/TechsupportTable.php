<?php
class HM_Techsupport_TechsupportTable extends HM_Db_Table
{
    protected $_name = "support_requests";
    protected $_primary = "support_request_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('support_requests.support_request_id ASC');
    }
}