<?php

class HM_Classifier_Type_TypeTable extends HM_Db_Table
{
    protected $_name = "classifiers_types";
    protected $_primary = "type_id";
    protected $_sequence = "S_94_1_CLASSIFIERS_TYPES";
 
    protected $_referenceMap = array(
        'Classifier' => array(
            'columns' => 'type_id',
            'refTableClass' => 'HM_Classifier_ClassifierTable',
            'refColumns' => 'type',
            'propertyName' => 'classifiers'
        )
    );// имя свойства текущей модели куда будут записываться модели зависимости
    

    public function getDefaultOrder()
    {
        return array('classifiers_types.name ASC');
    }
}