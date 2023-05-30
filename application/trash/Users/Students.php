<?php
class Users_Students extends Users {

	protected $_name = 'People';
	protected $_primary = 'MID';

	protected $_dependentTables = array();


	function fetchAll($select)
	{
	    $select = clone $select;
	    $select->from('People')
	           ->distinct()
	           ->join('Students', 'Students.MID = People.MID')
	           ->join('Courses', 'Courses.CID = Students.CID')
	           ->group('People.MID')
	           ->setIntegrityCheck(false);
	    return parent::fetchAll($select);
	}
}
?>