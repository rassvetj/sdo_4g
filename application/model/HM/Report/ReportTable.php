<?php

class HM_Report_ReportTable extends HM_Db_Table
{
    protected $_name = "reports";
    protected $_primary = "report_id";
    protected $_sequence = "S_55_1_REPORTS";

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

    protected $_referenceMap = array(
        'ReportRole' => array(
            'columns'       => 'report_id',
            'refTableClass' => 'HM_Report_Role_RoleTable',
            'refColumns'    => 'report_id',
            'propertyName'  => 'roles' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );

    public function getDefaultOrder()
    {
        return array('reports.name ASC');
    }
}