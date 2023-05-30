<?php
class Users_Claimants extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select->from('People')
	           ->distinct()
	           ->join('Claimants', 'Claimants.MID = People.MID', array())
	           ->join('Courses', 'Courses.CID = Claimants.CID', array())
	           ->group('People.MID')
	           ->setIntegrityCheck(false);

	    return parent::fetchAll($select);
	}
}
?>