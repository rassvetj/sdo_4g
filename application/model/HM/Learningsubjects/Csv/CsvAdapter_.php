<?php
class HM_Learningsubjects_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;

    public function getMappingArray()
    {
        return array(
            0 => 'id_external',
            1 => 'name',
            2 => 'direction',
            3 => 'specialisation',
            4 => 'hours',
            5 => 'control',
            6 => 'year',
        );
    }

}