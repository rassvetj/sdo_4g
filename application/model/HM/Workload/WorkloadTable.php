<?php
class HM_Workload_WorkloadTable extends HM_Db_Table
{
    protected $_name = "workload_violations"; 
    protected $_primary = "violation_id";
    protected $_sequence = "";
	
	
    protected $_referenceMap = array(
    
    );

    public function getDefaultOrder()
    {
        return array('workload_violations.violation_id ASC');
    }
}