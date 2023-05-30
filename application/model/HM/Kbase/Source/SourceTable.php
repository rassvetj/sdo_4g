<?php
class HM_Kbase_Source_SourceTable extends HM_Db_Table
{
    protected $_name = "kbase_source";
    protected $_primary = "source_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('kbase_source.source_id ASC');
    }
}

