<?php
class Roles_Custom extends Zend_Db_Table {

	protected $_name = 'permission_groups';
	protected $_primary = 'pmid';

	protected $_dependentTables = array();

	function __construct()
	{
	    parent::__construct();
	}
}
?>