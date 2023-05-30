<?php

class HM_Subject_Mark_Confirm_ConfirmTable extends HM_Db_Table
{
    protected $_name 	= 'courses_marks_confirm';
    protected $_primary = array('confirm_id');
    
    public function getDefaultOrder()
    {
        return array('courses_marks_confirm.confirm_id ASC');
    }
}