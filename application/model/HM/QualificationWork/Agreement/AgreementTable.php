<?php

class HM_QualificationWork_Agreement_AgreementTable extends HM_Db_Table
{
    protected $_name 	= "student_qualification_works_agreements";
    protected $_primary = array("agreement_id");


    public function getDefaultOrder()
    {
        return array('student_qualification_works_agreements.agreement_id ASC');
    }
}