<?php

class HM_Question_QuestionTable extends HM_Db_Table
{
    protected $_name = "list";
    protected $_primary = 'kod';

/*
     protected $_dependentTables = array(
        "HM_Role_StudentTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'TestQuestion' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Test_Question_QuestionTable',
            'refColumns'    => 'kod',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'tests' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'QuestionFiles' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Question_Files_FilesTable',
            'refColumns'    => 'kod',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'questionFiles' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('list.kod ASC');
    }
}