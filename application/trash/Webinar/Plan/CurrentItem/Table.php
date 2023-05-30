<?php
class Webinar_Plan_CurrentItem_Table extends HM_Db_Table {
    protected $_name = 'webinar_plan_current';
    protected $_primary = array('pointId');

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }
}