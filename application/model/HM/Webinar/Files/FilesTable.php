<?php

class HM_Webinar_Files_FilesTable extends HM_Db_Table
{
    protected $_name = "webinar_files";
    protected $_primary = array('webinar_id', 'file_id');

   // protected $_dependentTables = array("HM_Files_FilesTable");
    
    protected $_referenceMap = array(
        'File' => array(
           'columns'       => 'file_id',
           'refTableClass' => 'HM_Files_FilesTable',
           'refColumns'    => 'file_id',
           'propertyName'  => 'files'
        )
    );
    
    
    public function getDefaultOrder()
    {
        return array('webinar_files.webinar_id');
    }
}