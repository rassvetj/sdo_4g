<?php

class HM_Speciality_Group_GroupTable extends HM_Db_Table
{
    protected $_name = "tracks2group";
    protected $_primary = "id";
    protected $_sequence = "S_100_1_TRACKS2GROUP";

    //protected $_dependentTables = array("HM_Role_StudentTable");

    protected $_referenceMap = array(
        'Speciality' => array(
            'columns'       => 'trid',
            'refTableClass' => 'HM_Speciality_SpecialityTable',
            'refColumns'    => 'trid',
            'propertyName'  => 'specialities' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Group' => array(
            'columns'       => 'gid',
            'refTableClass' => 'HM_Group_GroupTable',
            'refColumns'    => 'gid',
            'propertyName'  => 'groups'
        )

    );

    public function getDefaultOrder()
    {
        return array('tracks2group.gid ASC');
    }
}