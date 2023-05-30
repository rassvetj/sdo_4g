<?php

class HM_Chain_Item_ItemTable extends HM_Db_Table
{
    protected $_name = "chain_item";
    protected $_primary = "id";
    protected $_sequence = "S_95_1_CHAIN_ITEM";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Chain' => array(
            'columns'       => 'chain',
            'refTableClass' => 'HM_Chain_ChainTable',
            'refColumns'    => 'id',
            'propertyName'  => 'chains' // имя свойства текущей модели куда будут записываться модели зависимости
        )

    );

    public function getDefaultOrder()
    {
        return array('chain_item.id ASC');
    }
}