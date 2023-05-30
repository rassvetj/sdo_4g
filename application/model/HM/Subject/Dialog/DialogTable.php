<?php

class HM_Subject_Dialog_DialogTable extends HM_Db_Table
{
    protected $_name = "subjects_interview";
    protected $_primary = array('interview_id');
    
    protected $_referenceMap = array(        	
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'user'        
		),
		'File' => array(
            'columns' 		=> 'interview_id',
            'refTableClass' => 'HM_Subject_Dialog_File_FileTable',
            'refColumns' 	=> 'interview_id',
            'propertyName' 	=> 'file'
		)	
    );
    public function getDefaultOrder()
    {
        return array('subjects_interview.interview_id ASC');
    }	
	
}