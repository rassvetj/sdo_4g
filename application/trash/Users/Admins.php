<?php
class Users_Admins extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select->from('People')
	           ->join('admins', 'admins.MID = People.MID')
	           ->group('People.MID')
	           ->setIntegrityCheck(false);

	    return parent::fetchAll($select);
	}
}
?>