<?php
class HM_Hostel_HostelTable extends HM_Db_Table
{
    protected $_name = "hostel_rooms"; 
    protected $_primary = "hostel_id";
    protected $_sequence = "";
	
	
    protected $_referenceMap = array(
    
    );

    public function getDefaultOrder()
    {
        return array('hostel_rooms.hostel_id ASC');
    }
}