<?php
class Webinar_History_Table extends HM_Db_Table {
    protected $_name = 'webinar_history';
    protected $_primary = array('id');
    protected $_rowClass = 'Webinar_History_Item';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }
}