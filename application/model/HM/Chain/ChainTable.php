<?php

class HM_Chain_ChainTable extends HM_Db_Table
{
    protected $_name = "chain";
    protected $_primary = "id";
    protected $_sequence = "S_94_1_CHAIN";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'Item' => array(
            'columns'       => 'id',
            'refTableClass' => 'HM_Chain_Item_ItemTable',
            'refColumns'    => 'chain',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'items' // имя свойства текущей модели куда будут записываться модели зависимости
        ),

    );

    public function getDefaultOrder()
    {
        return array('chain.name ASC');
    }
}