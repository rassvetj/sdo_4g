<?php
class HM_Classifier_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    protected $_skipLines = 1; // не менять - используется при генерации csv 

    public function getMappingArray()
    {
        return array(
            0 => 'parent',
            1 => 'name'
        );
    }
}