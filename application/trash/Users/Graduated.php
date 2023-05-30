<?php
class Users_Graduated extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select->from('People')
	           ->distinct()
	           ->join('graduated', 'graduated.MID = People.MID', array())
	           ->join('Courses', 'Courses.CID = graduated.CID', array())
	           ->group('People.MID')
	           ->setIntegrityCheck(false);

	    return parent::fetchAll($select);
	}
}
?>