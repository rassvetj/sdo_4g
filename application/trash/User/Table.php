<?php
class User_Table extends HM_Db_Table {

    protected $_name = 'People';
    protected $_primary = 'MID';
    protected $_rowClass = 'HM_User';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }

}


