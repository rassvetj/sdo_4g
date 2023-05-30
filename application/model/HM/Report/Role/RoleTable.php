<?php

class HM_Report_Role_RoleTable extends HM_Db_Table
{

    protected $_name = "reports_roles";
    protected $_primary = array("report_id", "role");

    protected $_referenceMap = array(
        'Report' => array(
            'columns'       => 'report_id',
            'refTableClass' => 'HM_Report_ReportTable',
            'refColumns'    => 'report_id',
            'propertyName'  => 'report' // имя свойства текущей модели куда будут записываться модели зависимости
        )
    );
    
}