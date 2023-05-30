<?php
class HM_StudentDebt_StudentDebtTable extends HM_Db_Table
{
    protected $_name = "student_debts";
    protected $_primary = "student_debt_id";
    protected $_sequence = "";

    protected $_referenceMap = array(
        
    );

    public function getDefaultOrder()
    {
        return array('student_debts.student_debt_id ASC');
    }
}