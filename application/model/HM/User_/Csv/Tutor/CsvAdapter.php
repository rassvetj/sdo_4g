<?php
class HM_User_Csv_Tutor_CsvAdapter extends HM_User_Csv_CsvAdapter
{
    public function getMappingArray()
    {
        return array(
            0  => 'mid_external',
            1  => 'LastName',
            2  => 'FirstName',
            3  => 'Patronymic',
            4  => 'Login',
            5  => 'EMail',
            6  => 'Password',
            7  => 'isTutor',
            8  => 'tags',
        );
    }

}