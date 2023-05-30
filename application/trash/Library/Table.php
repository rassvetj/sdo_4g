<?php
class Library_Table extends HM_Db_Table {

    protected $_name = 'library';
    protected $_primary = 'bid';
    protected $_rowClass = 'HM_LibraryItem';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }

}