<?php
class HM_Orgstructure_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        /*
        id подразделения
        наименование
        id родителя
        id пользователя
         */
        
        return array(
            0 => 'soid_external',
            1 => 'name',
            2 => 'owner_soid_external',
            3 => 'mid_external',
        );
    }

}