<?php
class Task_Table extends HM_Db_Table {

    protected $_name = 'schedule';
    protected $_primary = 'SHEID';
    protected $_rowClass = 'HM_Task';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }

}


