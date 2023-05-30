<?php

class HM_Role_Custom_CustomTable extends HM_Db_Table
{
    protected $_name = "permission_groups";
    protected $_primary = "pmid";

    protected $_dependentTables = array("HM_Role_Custom_Action_ActionTable", "HM_Role_Custom_Assign_AssignTable");

    protected $_referenceMap = array(
        'Action' => array(
            'columns'       => 'pmid',
            'refTableClass' => 'HM_Role_Custom_Action_ActionTable',
            'refColumns'    => 'pmid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'actions' // имя свойства текущей модели куда будут записываться модели зависимости
        ),
        'Assign' => array(
            'columns'       => 'pmid',
            'refTableClass' => 'HM_Role_Custom_Assign_AssignTable',
            'refColumns'    => 'pmid',
            'onDelete'      => self::CASCADE,
            'propertyName'  => 'assigns' // имя свойства текущей модели куда будут записываться модели зависимости
         )

    );

    public function getDefaultOrder()
    {
        return array('permission_groups.name ASC');
    }
}