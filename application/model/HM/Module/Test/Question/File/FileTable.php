<?php

class HM_Module_Test_Question_File_FileTable extends HM_Db_Table
{
    protected $_name = "file";
    //protected $_primary = array("tid", "kod");

    //protected $_dependentTables = array("HM_Role_StudentTable");
    
    protected $_referenceMap = array(
        'Question' => array(
            'columns'       => 'kod',
            'refTableClass' => 'HM_Module_Test_Question_QuestionTable',
            'refColumns'    => 'kod',
            'propertyName'  => 'questions' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('file.fnum ASC');
    }
}