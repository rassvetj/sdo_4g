<?php

class HM_Speciality_Assign_AssignTable extends HM_Db_Table
{
    protected $_name = "tracks2mid";
    protected $_primary = "trmid";
    protected $_sequence = "S_72_1_TRACKS2MID";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Speciality' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_SpecialityTable',
            'refColumns'    => 'trid',
            'propertyName'  => 'specialities' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            'propertyName'  => 'users'
        )

    );

    public function getDefaultOrder()
    {
        return array('tracks2mid.trmid ASC');
    }
}