<?php

class HM_Subject_Dialog_File_FileTable extends HM_Db_Table
{
    protected $_name = "subjects_interview_files";
    protected $_primary = array('interview_id', 'file_id');
    
    protected $_referenceMap = array(
        'Dialog' => array(
            'columns' 		=> 'interview_id',
            'refTableClass' => 'HM_Subject_Dialog_DialogTable',
            'refColumns' 	=> 'interview_id',
            'propertyName' 	=> 'dialog'
		),
    	'Files' => array(
            'columns' 		=> 'file_id',
            'refTableClass' => 'HM_Files_FilesTable',
            'refColumns' 	=> 'file_id',
            'propertyName' 	=> 'file'
		),
    );

    public function getDefaultOrder()
    {
        return array('interview_id');
    }
}