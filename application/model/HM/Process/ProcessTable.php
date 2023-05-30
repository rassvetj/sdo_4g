<?php

class HM_Process_ProcessTable extends HM_Db_Table
{
    protected $_name = "processes";
    protected $_primary = "process_id";
    protected $_sequence = "S_100_1_PROCESSES";

    protected $_dependentTables = array();

    protected $_referenceMap = array();

    public function getDefaultOrder()
    {
        return array('processes.process_id ASC');
    }
}