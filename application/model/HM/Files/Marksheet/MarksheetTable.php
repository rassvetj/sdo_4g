<?php

class HM_Files_Marksheet_MarksheetTable extends HM_Db_Table
{
    protected $_name = "files_marksheet";
    protected $_primary = 'file_id';
    
    public function getDefaultOrder()
    {
        return array('files_marksheet.file_id');
    }
}