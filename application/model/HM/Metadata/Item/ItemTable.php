<?php

class HM_Metadata_Item_ItemTable extends HM_Db_Table
{
    protected $_name = "metadata_items";
    protected $_primary = "item_id";
    protected $_sequence = "S_100_1_METADATA_ITEMS";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Group' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_Metadata_Group_GroupTable',
            'refColumns'    => 'group_id',
            'propertyName'  => 'groups' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('metadata_groups.name ASC');
    }
}