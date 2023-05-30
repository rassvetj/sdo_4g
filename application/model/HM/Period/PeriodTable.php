<?php

class HM_Period_PeriodTable extends HM_Db_Table
{
    protected $_name = "periods";
    protected $_primary = "lid";
    protected $_sequence = "S_46_1_PERIODS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    /*protected $_referenceMap = array(
        'Student' => array(
            'columns'       => 'CID',
            'refTableClass' => 'HM_Role_StudentTable',
            'refColumns'    => 'CID',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'students' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );*/

    public function getDefaultOrder()
    {
        return array('periods.name ASC');
    }
}