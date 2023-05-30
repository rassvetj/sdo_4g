<?php
class HM_Marksheet_MarksheetTable extends HM_Db_Table
{
    protected $_name = "marksheet_info";    
    protected $_primary = "marksheet_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
    );

    public function getDefaultOrder()
    {
        return array('marksheet_info.marksheet_id ASC');
    }
}