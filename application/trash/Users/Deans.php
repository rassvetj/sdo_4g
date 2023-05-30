<?php
class Users_Deans extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select = clone $select;
	    $select->from('People')
	           ->join('deans', 'deans.MID = People.MID')
	           ->group('People.MID')
	           ->setIntegrityCheck(false);

	    return parent::fetchAll($select);
	}
}
?>