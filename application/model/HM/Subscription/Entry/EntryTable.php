<?php

class HM_Subscription_Entry_EntryTable extends HM_Db_Table
{
    protected $_name = "subscription_entries";
    protected $_primary = "entry_id";
    protected $_sequence = "S_106_1_SUBSCRIPTION_ENTRIES";

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
        return array('subscription_entries.entry_id ASC');
    }
}