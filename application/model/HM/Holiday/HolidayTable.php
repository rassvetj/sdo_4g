<?php

class HM_Holiday_HolidayTable extends HM_Db_Table
{
    protected $_name = "holidays";
    protected $_primary = "id";
    protected $_sequence = "S_100_1_HOLIDAYS";

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'user_id',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        )
    );
}