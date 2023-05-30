<?php

class HM_StudentNotification_Agreement_AgreementTable extends HM_Db_Table
{
    protected $_name = "student_notification_agreements";
    protected $_primary = array("agreement_id");


    public function getDefaultOrder()
    {
        return array('student_notification_agreements.agreement_id ASC');
    }
}