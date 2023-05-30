<?php

class HM_Subject_Resource_ResourceTable extends HM_Db_Table
{
    protected $_name = "subjects_resources";
    protected $_primary = array("subject_id", "resource_id");

/*
     protected $_dependentTables = array(
        "HM_Role_StudentTable",
         "HM_Role_TeacherTable"
    );
*/    
    protected $_referenceMap = array(
        'Subject' => array(
            'columns' => 'subject_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns' => 'subid',
            'propertyName' => 'subjects'
        ),
        'Resource' => array(
            'columns' => 'resource_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns' => 'resource_id',
            'propertyName' => 'resources'
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('subjects_resources.resource_id ASC');
    }
}