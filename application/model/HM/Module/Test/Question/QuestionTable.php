<?php

class HM_Module_Test_Question_QuestionTable extends HM_Db_Table
{
    protected $_name = "list";
    protected $_primary = "kod";

    protected $_dependentTables = array(
        "HM_Module_Test_Question_Required_RequiredTable", 
        "HM_Module_Test_Question_File_FileTable"
    );

    protected $_referenceMap = array(
        'File' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Module_Test_Question_File_FileTable',
            'refColumns'    => 'kod',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'files' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Required' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Module_Test_Question_Required_RequiredTable',
            'refColumns'    => 'kod',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'required' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
    );

    public function getDefaultOrder()
    {
        return array('list.kod ASC');
    }
}