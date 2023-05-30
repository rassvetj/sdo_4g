<?php
class Library_Category_Table extends HM_Db_Table {

    protected $_name = 'library_categories';
    protected $_primary = 'catid';
    protected $_rowClass = 'HM_LibraryCategory';

    protected $_dependentTables = array();

    public function getDefaultOrder() {
        //return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
    }

}