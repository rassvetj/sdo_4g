<?php

class HM_QualificationWork_QualificationWorkTable extends HM_Db_Table
{
    protected $_name 	= "student_qualification_works";
    protected $_primary = array("work_id");


    public function getDefaultOrder()
    {
        return array('student_qualification_works.work_id ASC');
    }
}