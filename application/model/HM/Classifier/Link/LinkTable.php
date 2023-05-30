<?php

class HM_Classifier_Link_LinkTable extends HM_Db_Table
{
    protected $_name = "classifiers_links";
    protected $_primary = array("item_id", "classifier_id", "type");


     protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Role_TeacherTable",
     	"HM_Subject_SubjectTable"
    );
   
    protected $_referenceMap = array(
        'Classifier' => array(
            'columns' => 'classifier_id',
            'refTableClass' => 'HM_Classifier_ClassifierTable',
            'refColumns' => 'classifier_id',
            'propertyName' => 'classifiers'
        ),
        'Subject' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Subject_SubjectTable',
            'refColumns'    => 'subid',
            'propertyName'  => 'subject' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по type!
        ),
        'Resource' => array(
            'columns'       => 'item_id',
            'refTableClass' => 'HM_Resource_ResourceTable',
            'refColumns'    => 'resource_id',
            'propertyName'  => 'resource' // ВНИМАНИЕ!!! коллекцию нужно еще отфильтровать по type!
        ),
    );// имя свойства текущей модели куда будут записываться модели зависимости

    

    public function getDefaultOrder()
    {
        return array('classifiers_links.classifier_id ASC');
    }
}