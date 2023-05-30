<?php
class Users extends HM_Db_Table {

	protected $_name = 'People';
	protected $_primary = 'MID';
	protected $_rowClass = 'HM_Db_Table_Row_Users';

	protected $_dependentTables = array();

	public function getDefaultOrder() {
		return array('People.LastName ASC', 'People.FirstName ASC', 'People.Patronymic ASC');
	}
}
?>