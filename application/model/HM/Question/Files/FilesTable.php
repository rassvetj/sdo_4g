<?php

class HM_Question_Files_FilesTable extends HM_Db_Table
{
    protected $_name = "list_files";
    protected $_primary = array('file_id','kod');

    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Question_QuestionTable',
            'refColumns'    => 'kod',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'question' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Files' => array(
            'columns'       => 'file_id',
            'refTableClass' => 'HM_Files_FilesTable',
            'refColumns'    => 'file_id',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'files' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );// имя свойства текущей модели куда будут записываться модели зависимости

    public function getDefaultOrder()
    {
        return array('list_files.file_id ASC');
    }
}