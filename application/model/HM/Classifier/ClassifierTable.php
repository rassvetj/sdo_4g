<?php

class HM_Classifier_ClassifierTable extends HM_Db_Table_NestedSet
{
    protected $_name = "classifiers";
    protected $_left = 'lft';	
    protected $_right = 'rgt';
    protected $_level = 'level';
    protected $_primary = "classifier_id";
    protected $_sequence = "S_94_1_CLASSIFIERS";
    
    protected $_dependentTables = array(
        "HM_Classifier_Link_LinkTable", "HM_Classifier_Type_TypeTable"
    );
    

    protected $_referenceMap = array(
        'ClassifierLink' => array(
            'columns' => 'classifier_id',
            'refTableClass' => 'HM_Classifier_Link_LinkTable',
            'refColumns' => 'classifier_id',
            'propertyName' => 'classifierlinks'
        ),
        'ClassifierType' => array(
            'columns' => 'type',
            'refTableClass' => 'HM_Classifier_Type_TypeTable',
            'refColumns' => 'type_id',
            'propertyName' => 'types'
        )
    );

    public function getDefaultOrder()
    {
        return array('classifiers.name ASC');
    }
        
}