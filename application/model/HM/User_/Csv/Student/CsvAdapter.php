<?php
class HM_User_Csv_Student_CsvAdapter extends HM_User_Csv_CsvAdapter
{
    public function getMappingArray()
    {
        return array(
            0  => 'mid_external',
            1  => 'LastName',
            2  => 'FirstName',
            3  => 'Patronymic',
            4  => 'Gender',
            5  => 'BirthDate',
            6  => 'group_id_external',
            7  => 'status_1c',
            8  => 'Login',
            9  => 'Phone',
            10 => 'EMail',
            11 => 'CellularNumber',
        );
    }

}