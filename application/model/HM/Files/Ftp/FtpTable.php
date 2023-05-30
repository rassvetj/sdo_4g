<?php

class HM_Files_Ftp_FtpTable extends HM_Db_Table
{
    protected $_name = "files_ftp";
    protected $_primary = 'file_id';
    
    public function getDefaultOrder()
    {
        return array('files_ftp.file_id');
    }
}