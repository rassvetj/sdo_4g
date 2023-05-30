<?php
class HM_Payment_Code_CodeTable extends HM_Db_Table
{
    protected $_name = "student_codes";
    protected $_primary = "mid_external";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('student_codes.mid_external ASC');
    }
}