<?php

class HM_Files_FilesTable extends HM_Db_Table
{
    protected $_name = "files";
    protected $_primary = 'file_id';
    protected $_sequence = 'S_100_1_FILES';
    
    
    protected $_dependentTables = array("HM_Webinar_Files_FilesTable","HM_Files_Videoblock_VideoblockTable");
    
    protected $_referenceMap = array(
        'Webinar' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Webinar_Files_FilesTable',
            'refColumns'    => 'file_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'webinars' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Interview' => array(
            'columns' => 'file_id',
            'refTableClass' => 'HM_Interview_File_FileTable',
            'refColumns' => 'file_id',
            'propertyName' => 'interviews'
        ),
        'Videoblock' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Files_Videoblock_VideoblockTable',
            'refColumns'    => 'file_id',
            'propertyName'  => 'videos' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'QuestionFiles' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Question_Files_FilesTable',
            'refColumns'    => 'file_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'questionFiles' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
		'DialogFile' => array(
            'columns' => 'file_id',
            'refTableClass' => 'HM_Subject_Dialog_File_FileTable',
            'refColumns' => 'file_id',
            'propertyName' => 'dialog'
        ),
    );

    public function getDefaultOrder()
    {
        return array('files.file_id');
    }
}