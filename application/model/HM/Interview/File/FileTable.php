<?php

class HM_Interview_File_FileTable extends HM_Db_Table
{
    protected $_name = "interview_files";
    protected $_primary = array('interview_id', 'file_id');
    //protected $_sequence = "S_100_1_INTERVIEW_FILES";

    protected $_referenceMap = array(
        'Interview' => array(
            'columns' => 'interview_id',
            'refTableClass' => 'HM_Interview_InterviewTable',
            'refColumns' => 'interview_id',
            'propertyName' => 'interview'),
    	'Files' => array(
            'columns' => 'file_id',
            'refTableClass' => 'HM_Files_FilesTable',
            'refColumns' => 'file_id',
            'propertyName' => 'file')
    );

    public function getDefaultOrder()
    {
        return array('interview_id');
    }
}