<?php

class HM_Interview_InterviewTable extends HM_Db_Table
{
    protected $_name = "interview";
    protected $_primary = "interview_id";
    protected $_sequence = "S_100_1_INTERVIEW";

    
    
    protected $_referenceMap = array(
        'User' => array(
            'columns' => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns' => 'MID',
            'propertyName' => 'user'),
    	'File' => array(
            'columns' => 'interview_id',
            'refTableClass' => 'HM_Interview_File_FileTable',
            'refColumns' => 'interview_id',
            'propertyName' => 'file')
    );

    public function getDefaultOrder()
    {
        return array('interview_id');
    }
}