<?php

class HM_Orgstructure_OrgstructureTable extends HM_Db_Table_NestedSet
{
    protected $_name = "structure_of_organ";
    protected $_primary = "soid";
    protected $_left = 'lft';
    protected $_right = 'rgt';
    protected $_level = 'level';
    protected $_sequence = "S_61_1_STRUCTURE_OF_ORGAN";

    /*protected $_dependentTables = array(
        "HM_Role_StudentTable",
        "HM_Course_Item_ItemTable",
        "HM_Module_Test_TestTable"
    );*/

    protected $_referenceMap = array(
        'User' => array(
            'columns'       => 'mid',
            'refTableClass' => 'HM_User_UserTable',
            'refColumns'    => 'MID',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'user' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Descendant' => array(
            'columns'       => 'soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'owner_soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'descendants'
        ),
        'Parent' => array(
            'columns'       => 'owner_soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'parent'
        ),
        'Sibling' => array( // ВНИМАНИЕ! не совсем оно работает.. например условия вида 'Sibling.attr = ?'  в fetchAllDependenceJoinInner
            'columns'       => 'owner_soid',
            'refTableClass' => 'HM_Orgstructure_OrgstructureTable',
            'refColumns'    => 'owner_soid',
            //'onDelete'      => self::CASCADE,
            'propertyName'  => 'siblings'
        ),
    );

    public function getDefaultOrder()
    {
        return array('structure_of_organ.soid ASC');
    }
}