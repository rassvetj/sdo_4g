<?php
class HM_Internships_InternshipsTable extends HM_Db_Table
{
    protected $_name 			= "internship_requests";
    
    protected $_primary  		= "request_id";
    protected $_sequence 		= "";
	
    protected $_referenceMap 	= array(
    );

    public function getDefaultOrder()
    {
        return array('internship_requests.request_id ASC');
    }
}