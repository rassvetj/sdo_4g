<?php

class HM_Metadata_Group_GroupTable extends HM_Db_Table
{
    protected $_name = "metadata_groups";
    protected $_primary = "group_id";
    protected $_sequence = "S_100_1_METADATA_GROUPS";

    protected $_dependentTables = array(
        "HM_Metadata_Item_ItemTable",
    );

    protected $_referenceMap = array(
        'Item' => array(
            'columns'       => 'group_id',
            'refTableClass' => 'HM_Metadata_Item_ItemTable',
            'refColumns'    => 'group_id',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'items' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('metadata_groups.name ASC');
    }
}