<?php
class HM_Recruits_RecruitsTable extends HM_Db_Table
{    
    protected $_name = "recruits";    
    protected $_primary = "recruit_id";
    protected $_sequence = "";
	
    protected $_referenceMap = array(      
    );

    public function getDefaultOrder()
    {
        return array('recruits.recruit_id ASC');
    }
}