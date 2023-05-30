<?php
class HM_StudyGroup_Csv_CsvAdapter extends HM_Adapter_Csv_Abstract
{
    // Сколько первых строк будет пропущено
    protected $_skipLines = 1;
    
    public function getMappingArray()
    {
        return array(
            0  => 'id_external',
            1  => 'name',
            2  => 'faculty',
            3  => 'year',
            4  => 'education_type',
            5  => 'speciality',
            6  => 'course',
            7  => 'duration',
            8  => 'foundation_year',
            9  => 'programm_id_external',
            10 => 'programm_id_name',
            11 => 'begin_learning',
        );
    }

}