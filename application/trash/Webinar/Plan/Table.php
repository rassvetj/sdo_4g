<?php
class Webinar_Plan_Table extends HM_Db_Table {
    protected $_name = 'webinar_plan';
    protected $_primary = array('id');
    protected $_rowClass = 'Webinar_Plan_Item';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }
}
